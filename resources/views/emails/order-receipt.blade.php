<div class="max-w-md mx-auto bg-white border border-gray-200 shadow-xl rounded-2xl p-6 text-sm">

    <!-- Header -->
    <div class="text-center mb-4">
        <h2 class="text-xl font-bold text-green-600 tracking-wide">
            PARTY TRAY
        </h2>
        <p class="text-xs text-gray-500">
            Official Order Receipt
        </p>
    </div>

    <div class="border-t border-dashed border-gray-300 my-4"></div>

    <!-- Order Info -->
    <div class="space-y-1 text-gray-700">
        <p><span class="font-semibold">Order #:</span> {{ $order->order_number }}</p>
        <p>
            <span class="font-semibold">Status:</span>
            <span class="px-2 py-0.5 rounded-full text-xs
                @if($order->status == 'pending') bg-yellow-100 text-yellow-700
                @elseif($order->status == 'preparing') bg-blue-100 text-blue-700
                @elseif($order->status == 'out_of_delivery') bg-purple-100 text-purple-700
                @elseif(in_array($order->status, ['delivered','completed'])) bg-green-100 text-green-700
                @else bg-gray-100 text-gray-600
                @endif">
                {{ ucfirst(str_replace('_',' ', $order->status)) }}
            </span>
        </p>
        <p><span class="font-semibold">Date:</span> {{ $order->created_at->format('M d, Y h:i A') }}</p>
    </div>

    <div class="border-t border-dashed border-gray-300 my-4"></div>

    <!-- Items -->
    <div>
        <h3 class="font-semibold text-gray-800 mb-2">
            Order Items
        </h3>

        <div class="space-y-2">
            @foreach($order->items as $item)
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-medium text-gray-800">
                            {{ $item->food->name ?? 'Deleted Food' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            ₱{{ number_format($item->price,2) }} × {{ $item->quantity }}
                        </p>
                    </div>
                    <div class="font-semibold text-gray-900">
                        ₱{{ number_format($item->subtotal, 2) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="border-t border-dashed border-gray-300 my-4"></div>

    <!-- Total -->
    <div class="flex justify-between items-center text-base">
        <span class="font-semibold text-gray-800">Total</span>
        <span class="text-green-600 font-bold text-lg">
            ₱{{ number_format($order->total, 2) }}
        </span>
    </div>

    <div class="border-t border-dashed border-gray-300 my-4"></div>

    <!-- Footer -->
    <div class="text-center text-xs text-gray-400">
        <p>Thank you for ordering with us 🍽</p>
        <p>Please keep this receipt for your reference.</p>
    </div>

</div>