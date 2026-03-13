<p>Hello {{ $order->customer_name ?? 'Customer' }},</p>

<p>Your order <strong>{{ $order->order_number }}</strong> has been updated.</p>

<p><strong>Approval:</strong>
    @if(($order->approval_status ?? 'pending') === 'approved')
        Approved
    @elseif(($order->approval_status ?? 'pending') === 'disapproved')
        Disapproved
    @else
        Awaiting approval
    @endif
</p>

<p><strong>Order Status:</strong> {{ $statusLabel }}</p>

@if(!empty($order->approval_note))
    <p><strong>Admin Note:</strong> {{ $order->approval_note }}</p>
@endif

<p><strong>Total:</strong> ₱{{ number_format((float) $order->total, 2) }}</p>

<p>You can track your order in the system for the latest progress.</p>

<p>Thank you.</p>
