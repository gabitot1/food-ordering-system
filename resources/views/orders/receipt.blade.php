<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body { font-family: sans-serif; }
        h2 { margin-bottom: 0; }
        hr { margin: 10px 0; }
    </style>
</head>
<body>

<h2>Order Receipt</h2>
<p>Order #: {{ $order->order_number }}</p>
<p>Date: {{ $order->created_at->format('M d, Y h:i A') }}</p>

<hr>

<p><strong>Customer:</strong> {{ $order->customer_name }}</p>
<p><strong>Delivery:</strong> {{ $order->delivery_option }}</p>

<hr>

<h4>Items:</h4>

@foreach($order->items as $item)
    <p>
        {{ $item->food->name ?? 'Deleted Food' }}
        (x{{ $item->quantity }})
        - ${{ number_format($item->subtotal,2) }}
    </p>
@endforeach

<hr>

<h3>Total: ${{ number_format($order->total,2) }}</h3>

</body>
</html>
