<?php

namespace App\Http\Controllers;

use App\Models\OrderItems;
use App\Models\Orders;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
   public function store(Request $request)
{
    $request->validate([
        'customer_name' => 'required',
        'address' => 'required',
        'email' => 'required|email',
        'contact_number' => 'required',
        'delivery_option' => 'required',
        'payment_method' => 'required',
    ]);

    $cart = session()->get('cart');

    if (!$cart || count($cart) == 0) {
        return back()->with('error', 'Cart is empty.');
    }

    $total = 0;

    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // CREATE ORDER
    $order = Orders::create([
        'customer_name' => $request->customer_name,
        'address' => $request->address,
        'email' => $request->email,
        'contact_number' => $request->contact_number,
        'delivery_option' => $request->delivery_option,
        'payment_method' => $request->payment_method,
        'total' => $total,
        'status' => 'pending',
    ]);

    // SAVE ORDER ITEMS
    foreach ($cart as $id => $item) {
        OrderItems::create([
            'order_id' => $order->id,
            'food_id' => $id,
            'quantity' => $item['quantity'],
            'price' => $item['price'],
        ]);
    }

    // CLEAR CART
    session()->forget('cart');

    return redirect()->route('cart.index')
        ->with('success', 'Order placed successfully!');
}
}
