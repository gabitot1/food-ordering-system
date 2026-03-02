<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\OrderItems;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // ADMIN ORDERS LIST
    public function adminOrders(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $status = $request->query('status');
        $q = trim($request->query('q', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ordersQuery = Orders::latest();

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        if ($q !== '') {
            $ordersQuery->where(function ($query) use ($q) {
                $query->where('customer_name', 'like', "%{$q}%")
                      ->orWhere('contact_number', 'like', "%{$q}%");
            });
        }

        if ($dateFrom) {
            $ordersQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $ordersQuery->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $ordersQuery->paginate(5);

        return view('admin.orders', compact('orders', 'status', 'q', 'dateFrom', 'dateTo'));
    }

    // ADMIN UPDATE ORDER
    public function adminUpdateOrder(Request $request, Orders $order)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,preparing,delivered,cancelled',
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Order Updated');
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

    return view('admin.dashboard', compact(
        'totalOrders',
        'totalSales',
        'todayOrders',
        'todaySales',
        'pendingCount',
        'preparingCount',
        'salesDays',
        'salesTotals',
        'topFoods',
        'monthlyLabels',
        'monthlySales'
    ));
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
