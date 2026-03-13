<?php

namespace App\Http\Controllers;

use App\Mail\OrderStatusUpdatedMail;
use App\Models\Orders;
use App\Models\OrderItems;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class CartController extends Controller
{
    // ADMIN ORDERS LIST
    public function adminOrders(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $status = $request->query('status');
        $approvalStatus = $request->query('approval_status');
        $q = trim($request->query('q', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ordersQuery = Orders::latest();

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        if ($approvalStatus) {
            $ordersQuery->where('approval_status', $approvalStatus);
        }

        if ($q !== '') {
            $ordersQuery->where(function ($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                      ->orWhere('customer_name', 'like', "%{$q}%")
                      ->orWhere('contact_number', 'like', "%{$q}%");
            });
        }

        if ($dateFrom) {
            $ordersQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $ordersQuery->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $ordersQuery->paginate(5)->withQueryString()
            ->through(function ($order) {
                $status = $order->status ?? 'pending';
                $approvalStatus = $order->approval_status ?? 'pending';
                $pay = $order->payment_status ?? 'unpaid';

                $statusColor = match ($status) {
                    'pending' => 'text-yellow-600 bg-yellow-50',
                    'preparing' => 'text-blue-600 bg-blue-50',
                    'out_of_delivery' => 'text-purple-600 bg-purple-50',
                    'delivered' => 'text-green-600 bg-green-50',
                    default => 'text-gray-600 bg-gray-50',
                };

                $payColor = match ($pay) {
                    'unpaid' => 'text-gray-600 bg-gray-100',
                    'paid' => 'text-green-600 bg-green-50',
                    'refunded' => 'text-purple-600 bg-purple-50',
                    default => 'text-gray-600 bg-gray-100',
                };

                $approvalColor = match ($approvalStatus) {
                    'approved' => 'text-green-600 bg-green-50',
                    'disapproved' => 'text-red-600 bg-red-50',
                    default => 'text-amber-700 bg-amber-50',
                };

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? ('ORD-' . $order->id),
                    'customer_name' => $order->customer_name ?? 'Guest Customer',
                    'total' => (float) ($order->total ?? 0),
                    'items_count' => $order->items?->count() ?? 0,
                    'purchase_time_label' => $order->created_at?->format('M d, Y • h:i A'),
                    'schedule_type_label' => ($order->delivery_option ?? 'pickup') === 'delivery' ? 'Deliver by' : 'Pick up by',
                    'scheduled_time_label' => $order->is_scheduled
                        ? (($order->scheduled_for?->format('M d, Y') ?? '') . ($order->schedule_slot ? ' • ' . $order->schedule_slot : ''))
                        : 'Order now',
                    'status' => $status,
                    'approval_status' => $approvalStatus,
                    'approval_note' => $order->approval_note,
                    'payment_status' => $pay,
                    'payment_method' => $order->payment_method ?? 'cash',
                    'status_label' => ucfirst(str_replace('_', ' ', $status)),
                    'status_color' => $statusColor,
                    'approval_label' => match ($approvalStatus) {
                        'approved' => 'Approved',
                        'disapproved' => 'Disapproved',
                        default => 'Awaiting approval',
                    },
                    'approval_color' => $approvalColor,
                    'payment_label' => ucfirst($pay),
                    'payment_color' => $payColor,
                ];
            });

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $status,
                'approval_status' => $approvalStatus,
                'q' => $q,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'tabs' => [
                ['key' => '', 'label' => 'All'],
                ['key' => 'pending', 'label' => 'Pending'],
                ['key' => 'preparing', 'label' => 'Preparing'],
                ['key' => 'out_of_delivery', 'label' => 'Out of Delivery'],
                ['key' => 'delivered', 'label' => 'Delivered'],
            ],
            'status_options' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'preparing', 'label' => 'Preparing'],
                ['value' => 'out_of_delivery', 'label' => 'Out of Delivery'],
                ['value' => 'delivered', 'label' => 'Delivered'],
                ['value' => 'cancelled', 'label' => 'Cancelled'],
            ],
            'approval_status_options' => [
                ['value' => 'pending', 'label' => 'Awaiting Approval'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'disapproved', 'label' => 'Disapproved'],
            ],
            'payment_status_options' => [
                ['value' => 'unpaid', 'label' => 'Unpaid'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'refunded', 'label' => 'Refunded'],
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    // ADMIN UPDATE ORDER
    public function adminUpdateOrder(Request $request, Orders $order)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,preparing,out_of_delivery,delivered,cancelled',
            'approval_status' => 'required|in:pending,approved,disapproved',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
            'approval_note' => 'nullable|string|max:500',
        ]);

        $previousApprovalStatus = $order->approval_status ?? 'pending';
        $previousStatus = $order->status ?? 'pending';
        $nextApprovalStatus = $request->approval_status;
        $nextStatus = $request->status;
        $approvalNote = trim((string) $request->input('approval_note', ''));

        if ($nextApprovalStatus === 'disapproved') {
            $nextStatus = 'cancelled';
        } elseif ($nextApprovalStatus === 'pending' && !in_array($nextStatus, ['pending', 'cancelled'], true)) {
            $nextStatus = 'pending';
        }

        if ($nextApprovalStatus === 'disapproved' && $approvalNote === '') {
            return back()->withErrors([
                'approval_note' => 'A note is required when disapproving an order.',
            ]);
        }

        if ($nextApprovalStatus !== 'approved' && !in_array($nextStatus, ['pending', 'cancelled'], true)) {
            return back()->withErrors([
                'status' => 'Order must be approved before it can move to preparation or delivery.',
            ]);
        }

        $updateData = [
            'status' => $nextStatus,
            'approval_status' => $nextApprovalStatus,
        ];

        if ($nextApprovalStatus === 'approved') {
            $updateData['approved_by'] = Auth::id();
            $updateData['approved_at'] = now();
            $updateData['approval_note'] = null;

            if ($order->approval_status === 'disapproved' && $order->status === 'cancelled' && $nextStatus === 'cancelled') {
                $updateData['status'] = 'pending';
            }
        } elseif ($nextApprovalStatus === 'disapproved') {
            $updateData['approved_by'] = Auth::id();
            $updateData['approved_at'] = now();
            $updateData['approval_note'] = $approvalNote;
            $updateData['status'] = 'cancelled';
        } else {
            $updateData['approved_by'] = null;
            $updateData['approved_at'] = null;
            $updateData['approval_note'] = null;

            if ($order->approval_status === 'disapproved' && $order->status === 'cancelled') {
                $updateData['status'] = 'pending';
            }
        }

        if ($request->filled('payment_status')) {
            $updateData['payment_status'] = $request->payment_status;
        }

        $emailBeforeUpdate = $order->email;

        $order->update($updateData);
        $order->refresh();

        $shouldSendStatusEmail =
            ($previousApprovalStatus !== 'approved' && $order->approval_status === 'approved') ||
            ($previousApprovalStatus !== 'disapproved' && $order->approval_status === 'disapproved') ||
            ($previousStatus !== 'preparing' && $order->status === 'preparing') ||
            ($previousStatus !== 'out_of_delivery' && $order->status === 'out_of_delivery');

        if (!empty($emailBeforeUpdate) && $shouldSendStatusEmail) {
            try {
                Log::info('Sending order status update email.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_email' => $emailBeforeUpdate,
                    'status' => $order->status,
                    'approval_status' => $order->approval_status,
                ]);

                Mail::to($emailBeforeUpdate)->send(new OrderStatusUpdatedMail($order));

                Log::info('Order status update email sent.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_email' => $emailBeforeUpdate,
                ]);
            } catch (\Throwable $e) {
                Log::error('Order status update email sending failed.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_email' => $emailBeforeUpdate,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Order updated.');
    }

    // ADMIN DASHBOARD
   public function adminDashboard()
{
    if (!auth()->check() || !auth()->user()->is_admin) {
        abort(403);
    }

    $today = now();

    // BASIC COUNTS
    $totalOrders = Orders::count();
    $totalSales = Orders::sum('total');

    $todayOrders = Orders::whereDate('created_at', $today)->count();
    $todaySales = Orders::whereDate('created_at', $today)->sum('total');

    $pendingCount = Orders::where('status', 'pending')->count();
    $preparingCount = Orders::where('status', 'preparing')->count();

    // LAST 7 DAYS SALES
    $salesDays = [];
    $salesTotals = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);

        $salesDays[] = $date->format('M d');

        $salesTotals[] = Orders::whereDate('created_at', $date)
            ->sum('total');
    }

    // TOP 5 FOODS (ONLY IF YOU HAVE order_items TABLE)
    $topFoods = \App\Models\OrderItems::select('food_id', \DB::raw('SUM(quantity) as total_qty'))
        ->groupBy('food_id')
        ->orderByDesc('total_qty')
        ->with('food')
        ->take(5)
        ->get();

    // LAST 6 MONTHS SALES
    $monthlyLabels = [];
    $monthlySales = [];

    for ($i = 5; $i >= 0; $i--) {
        $start = now()->startOfMonth()->subMonths($i);
        $end = now()->endOfMonth()->subMonths($i);

        $monthlyLabels[] = $start->format('M Y');

        $monthlySales[] = Orders::whereBetween('created_at', [$start, $end])
            ->sum('total');
    }

    return Inertia::render('Admin/Dashboard', [
        'stats' => [
            'total_orders' => (int) $totalOrders,
            'total_sales' => (float) $totalSales,
            'today_orders' => (int) $todayOrders,
            'today_sales' => (float) $todaySales,
            'pending_count' => (int) $pendingCount,
            'preparing_count' => (int) $preparingCount,
        ],
        'sales' => [
            'days' => $salesDays,
            'totals' => array_map(fn ($v) => (float) $v, $salesTotals),
            'monthly_labels' => $monthlyLabels,
            'monthly_sales' => array_map(fn ($v) => (float) $v, $monthlySales),
        ],
        'top_foods' => $topFoods->map(function ($row) {
            $qty = (int) $row->total_qty;
            return [
                'food_id' => $row->food_id,
                'name' => $row->food->name ?? 'Food Deleted',
                'total_qty' => $qty,
                'bar_width' => min($qty * 5, 100),
            ];
        })->values(),
    ]);
}


    // EXPORT PDF
    public function exportOrdersPdf(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $query = Orders::query();

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $orders = $query->latest()->get();

        $pdf = Pdf::loadView('admin.orders-pdf', compact('orders'))
                  ->setPaper('A4', 'portrait');

        return $pdf->download('orders-report.pdf');
    }

}
