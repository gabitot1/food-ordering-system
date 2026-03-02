<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif">

<h2>Thank you for your order!</h2>

<p>
    <strong>Order #:</strong> {{ $order->order_number }} <br>
    <strong>Status:</strong> {{ ucfirst($order->status) }} <br>
    <strong>Total:</strong> ₱{{ number_format($order->total, 2) }}
</p>

<table width="100%" cellpadding="6" cellspacing="0" border="1">
    <thead>
        <tr>
            <th align="left">Food</th>
            <th align="left">Qty</th>
            <th align="left">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->food->name ?? 'Food Deleted' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₱{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p>
    <strong>Total Paid:</strong> ₱{{ number_format($order->total, 2) }}
</p>

<p>
    You can also download your receipt anytime from your orders page.
</p>

</body>
</html>
