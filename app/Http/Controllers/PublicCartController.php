<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Foods;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\User;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderReceiptMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class PublicCartController extends Controller
{
    private function sessionOrderIds(): array
    {
        return session()->get('my_orders', []);
    }

    private function ensureSessionOwnsOrderId(int $orderId): void
    {
        if (!in_array($orderId, $this->sessionOrderIds(), true)) {
            abort(403);
        }
    }

    // SHOW CART
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    // REMOVE FROM CART
    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Removed from cart');
    }

    // CHECKOUT
    public function checkout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'contact_number' => 'required',
            'delivery_option' => 'required',
            'payment_method' => 'required',
            'is_scheduled' => 'nullable|boolean',
            'scheduled_date' => 'nullable|date|after_or_equal:today',
            'scheduled_for' => 'nullable|date|after_or_equal:today',
            'schedule_slot' => 'required_if:is_scheduled,1|in:09:00-11:00,11:00-13:00,13:00-15:00,15:00-17:00',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Cart is empty');
        }

        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $customerEmail = $request->email;

        $isScheduled = $request->boolean('is_scheduled');
        $scheduledFor = null;
        $scheduleSlot = null;

        if ($isScheduled) {
            $scheduleDate = $request->input('scheduled_date');
            if (empty($scheduleDate) && $request->filled('scheduled_for')) {
                $scheduleDate = Carbon::parse($request->input('scheduled_for'))->toDateString();
            }

            if (empty($scheduleDate)) {
                return back()
                    ->withInput()
                    ->withErrors(['scheduled_date' => 'The schedule date field is required when scheduled is enabled.']);
            }

            $scheduleSlot = $request->schedule_slot;
            [$startTime] = explode('-', $scheduleSlot);
            $scheduledFor = Carbon::parse($scheduleDate . ' ' . $startTime);

            $slotCount = Orders::query()
                ->where('is_scheduled', true)
                ->whereDate('scheduled_for', $scheduleDate)
                ->where('schedule_slot', $scheduleSlot)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($slotCount >= 10) {
                return back()
                    ->withInput()
                    ->withErrors(['schedule_slot' => 'Selected schedule slot is already full.']);
            }
        }

        $orderData = [
            'order_number' => 'ORD-' . now()->format('YmdHis') . rand(10, 99),
            'customer_name' => $request->customer_name,
            'address' => $request->address,
            'department' => $request->department ?? null,
            'contact_number' => $request->contact_number,
            'delivery_option' => $request->delivery_option,
            'payment_method' => $request->payment_method,
            'total' => $total,
            'status' => 'pending',
            'instruction' => $request->instruction,
            'payment_status' => $request->payment_method === 'cash' ? 'unpaid' : 'paid',
            'is_scheduled' => $isScheduled,
            'scheduled_for' => $scheduledFor,
            'schedule_slot' => $scheduleSlot,
        ];

        if (Schema::hasColumn('orders', 'email')) {
            $orderData['email'] = $customerEmail;
        }

        $order = Orders::create($orderData);

        foreach ($cart as $id => $item) {
            OrderItems::create([
                'order_id' => $order->id,
                'food_id' => $item['food_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
                'instruction' => $item['instruction'] ?? null,
            ]);
        }

        $orders = session()->get('my_orders', []);
        $orders[] = $order->id;
        session()->put('my_orders', $orders);

        $order->load('items.food');

        try {
            Mail::to($customerEmail)->send(new OrderReceiptMail($order));

            $adminRecipients = collect();

            if (config('mail.admin_address')) {
                $adminRecipients->push(config('mail.admin_address'));
            }

            $adminRecipients = $adminRecipients
                ->merge(
                    User::query()
                        ->where('is_admin', 1)
                        ->whereNotNull('email')
                        ->pluck('email')
                )
                ->filter()
                ->unique()
                ->values();

            foreach ($adminRecipients as $adminEmail) {
                Mail::to($adminEmail)->send(new AdminNewOrderMail($order));
            }
        } catch (\Throwable $e) {
            Log::error('Checkout email sending failed.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => $e->getMessage(),
            ]);
        }

        session()->forget('cart');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'track_url' => route('orders.track', $order->order_number),
            ]);
        }

        return redirect()
            ->route('orders.track', $order->order_number)
            ->with('success', 'Order placed successfully! Order #' . $order->order_number);
    }

    public function track($order_number)
    {
        $order = Orders::where('order_number', $order_number)
            ->with('items.food')
            ->first();

        if (!$order) {
            return 'Order not found: ' . $order_number;
        }

        $this->ensureSessionOwnsOrderId($order->id);

        return view('orders.track', compact('order'));
    }

    public function increase($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
        }

        session()->put('cart', $cart);

        return back();
    }

    public function decrease($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            if ($cart[$id]['quantity'] > 1) {
                $cart[$id]['quantity']--;
            } else {
                unset($cart[$id]);
            }
        }

        session()->put('cart', $cart);

        return back();
    }

    public function searchOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required',
        ]);

        $orderIds = $this->sessionOrderIds();
        $order = Orders::whereIn('id', $orderIds)
            ->where('order_number', $request->order_number)
            ->first();

        if (!$order) {
            return back()->with('error', 'Order not found in your history.');
        }

        return redirect()->route('orders.track', $order->order_number);
    }

    public function myOrders(Request $request)
    {
        $orderIds = session()->get('my_orders', []);

        $query = Orders::whereIn('id', $orderIds)
            ->with('items.food')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(3)->withQueryString();

        if ($request->ajax()) {
            return view('orders.partials.orders-list', compact('orders'))->render();
        }

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $this->ensureSessionOwnsOrderId((int) $id);

        $order = Orders::with('items.food')->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    public function cancelOrder(Orders $order)
    {
        $this->ensureSessionOwnsOrderId($order->id);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Order Cancelled');
    }

    public function receipt($id)
    {
        $this->ensureSessionOwnsOrderId((int) $id);

        $order = Orders::with('items.food')->findOrFail($id);
        $pdf = Pdf::loadView('orders.receipt', compact('order'));

        return $pdf->stream('receipt-' . $order->order_number . '.pdf');
    }

    public function downloadReceipt($id)
    {
        $this->ensureSessionOwnsOrderId((int) $id);

        $order = Orders::with('items.food')->findOrFail($id);

        $pdf = Pdf::loadView('orders.recept-pdf', compact('order'));

        return $pdf->download('receipt-' . $order->order_number . '.pdf');
    }

    public function receiptModal(Orders $order)
    {
        $this->ensureSessionOwnsOrderId($order->id);

        $order->load('items.food');

        return view('orders.receipt-modal', compact('order'));
    }

    public function add(Request $request)
    {
        $food = Foods::findOrFail($request->food_id);

        $cart = session()->get('cart', []);

        $quantity = $request->quantity ?? 1;
        $instruction = $request->instruction ?? null;

        $cart[$food->id] = [
            'food_id' => $food->id,
            'name' => $food->name,
            'price' => $food->price,
            'quantity' => isset($cart[$food->id])
                ? $cart[$food->id]['quantity'] + $quantity
                : $quantity,
            'instruction' => $instruction,
        ];

        session()->put('cart', $cart);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Added to cart');
    }

    public function dashboard()
    {
        $orderIds = session()->get('my_orders', []);

        if (empty($orderIds)) {
            $orders = collect();
        } else {
            $orders = Orders::whereIn('id', $orderIds)
                ->latest()
                ->get();
        }

        $totalOrders = $orders->count();
        $pendingOrders = $orders->where('status', 'pending')->count();
        $completedOrders = $orders->where('status', 'completed')->count();

        return view('dashboard.public', compact(
            'orders',
            'totalOrders',
            'pendingOrders',
            'completedOrders'
        ));
    }
}
