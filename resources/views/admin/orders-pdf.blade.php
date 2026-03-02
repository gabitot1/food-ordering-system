<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orders Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { margin-bottom: 0; }
        .small { font-size: 11px; color: #555; }
    </style>
</head>
<body>

<h2>Orders Report</h2>
<p class="small">
    Generated at: {{ now()->format('M d, Y h:i A') }}
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Order No</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Total</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $i => $order)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>{{ ucfirst($order->payment_status) }}</td>
                <td>₱{{ number_format($order->total, 2) }}</td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
