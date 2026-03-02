<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Foods;
use App\Models\OrderItems;
use App\Models\Orders;
use Barryvdh\DomPDF\Facade\Pdf;

class PublicCartController extends Controller
{
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
            'contact_number' => 'required',
            'delivery_option' => 'required',
            'payment_method' => 'required',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Cart is empty');
        }

        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $order = Orders::create([
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
        ]);

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

        session()->forget('cart');

        return redirect()->route('orders.track', $order->order_number);
    }

    public function track($order_number)
    {
        $order = Orders::where('order_number', $order_number)
            ->with('items.food')
            ->first();

        if (!$order) {
            return 'Order not found: ' . $order_number;
        }

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

        $order = Orders::where('order_number', $request->order_number)->first();

        if (!$order) {
            return back()->with('error', 'Orders not found');
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
        $orderIds = session()->get('my_orders', []);

        if (!in_array($id, $orderIds)) {
            abort(403);
        }

        $order = Orders::with('items.food')->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    public function cancelOrder(Orders $order)
    {
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
        $order = Orders::with('items.food')->findOrFail($id);
        $pdf = Pdf::loadView('orders.receipt', compact('order'));

        return $pdf->stream('receipt-' . $order->order_number . '.pdf');
    }

    public function downloadReceipt($id)
    {
        $order = Orders::with('items.food')->findOrFail($id);

        $pdf = Pdf::loadView('orders.recept-pdf', compact('order'));

        return $pdf->download('receipt-' . $order->order_number . '.pdf');
    }

    public function receiptModal(Orders $order)
    {
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
