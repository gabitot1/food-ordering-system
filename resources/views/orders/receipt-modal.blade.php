<div class="flex justify-center">

    <div class="bg-white w-80 shadow-xl p-6 text-sm font-mono text-black relative">

        <!-- Store Header -->
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold tracking-wider">
                PARTY TRAY
            </h2>
            <p>F&b, 66 United Street, Mandaluyong</p>
            <p>--------------------------------</p>
        </div>

        <!-- Order Info -->
        <div class="mb-3">
            <p>Order #: {{ $order->order_number }}</p>
            <p>Date: {{ $order->created_at->format('M d, Y h:i A') }}</p>
            <p>Name: {{ $order->customer_name }}</p>
            <p>--------------------------------</p>
        </div>

        <!-- Items -->
        @foreach($order->items as $item)
            <div class="mb-2">
                <div class="flex justify-between">
                    <span>{{ $item->food->name ?? 'Deleted' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>
                        {{ $item->quantity }} × ₱{{ number_format($item->price,2) }}
                    </span>
                    <span>
                        ₱{{ number_format($item->subtotal,2) }}
                    </span>
                </div>
            </div>
        @endforeach

        <p>--------------------------------</p>

        <!-- Total -->
        <div class="flex justify-between font-bold text-base mt-2">
            <span>TOTAL</span>
            <span>₱{{ number_format($order->total,2) }}</span>
        </div>

        <p class="mt-3">--------------------------------</p>

        <!-- Status -->
        <div class="text-center mt-3">
            <p>Status: {{ ucfirst(str_replace('_',' ', $order->status)) }}</p>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-xs">
            <p>Thank you for your order!</p>
            <p>Please come again </p>
        </div>

        <!-- Buttons -->
        <div class="mt-6 text-center space-y-2">
            <button onclick="window.print()"
                class="bg-black text-white px-4 py-2 w-full rounded hover:bg-gray-800 transition">
                Print
            </button>

            <a href="{{ route('orders.receipt.download', $order->id) }}"
               class="block bg-green-600 text-white px-4 py-2 w-full rounded hover:bg-green-700 transition">
                Download 
            </a>
        </div>

    </div>

</div>
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .font-mono, .font-mono * {
        visibility: visible;
    }
    .font-mono {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm;
    }
}
</style>
