<h2>Order Receipt</h2>

<p>Order Number: {{ $order->order_number }}</p>
<p>Status: {{ $order->status }}</p>

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
