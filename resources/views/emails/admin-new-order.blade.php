<h2>New Order Received</h2>

<p><strong>Order Number:</strong> {{ $order->order_number }}</p>
<p><strong>Customer:</strong> {{ $order->customer_name }}</p>
<p><strong>Contact:</strong> {{ $order->contact_number }}</p>
<p><strong>Address:</strong> {{ $order->address }}</p>
<p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
<p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
<p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>

<hr>

@foreach($order->items as $item)
    <p>
        {{ $item->food->name ?? 'Deleted Food' }}
        x {{ $item->quantity }}
        = ₱{{ number_format($item->subtotal, 2) }}
    </p>
@endforeach

<hr>

<p><strong>Total: ₱{{ number_format($order->total, 2) }}</strong></p>

