
    @foreach($orders as $order)
            <div class="bg-white p-6 rounded shadow mb-6">
                <div class="flex justify-between">
                    <div>
                        <h3 class="font-bold">
                            Order #: {{ $order->order_number }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $order->created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="font-semibold text-green-700">
                            ₱{{ number_format($order->total, 2) }}
                        </p>
                        <p class="text-sm capitalize">
                            {{ $order->status }}
                        </p>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('orders.show', $order->id) }}"
                    class="text-blue-600 hover:underline">
                        View Details
                    </a>
                </div>
            </div>
              <div class="mt-8 flex justify-center">
            {{ $orders->links() }}
        </div>
        @endforeach
       

       

