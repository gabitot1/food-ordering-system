<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-green-800">
            Order Tracking
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-10">

        <div class="bg-white shadow rounded-lg p-6">

            <h3 class="text-lg font-bold mb-4">
                Order #: {{ $order->order_number }}
            </h3>

            <p><strong>Name:</strong> {{ $order->customer_name }}</p>
            <p><strong>Total:</strong> ₱{{ number_format($order->total,2) }}</p>
            <p><strong>Status:</strong> 
                <span class="px-3 py-1 text-xs rounded-full
    {{ $order->payment_status === 'paid'
        ? 'bg-green-100 text-green-700'
        : 'bg-red-100 text-red-700' }}">
    {{ ucfirst($order->payment_status) }}
</span>
            </p>
            <p><strong>Delivery:</strong> {{ ucfirst($order->delivery_option) }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
            
            
           <div class="mt-4">
            <a href="{{ route('orders.index') }}"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                Back to Orders
            </a>
        </div>
        </div>

    </div>
</x-app-layout>
