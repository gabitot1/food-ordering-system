@if($latestOrder && in_array($latestOrder->status, ['preparing','out_of_delivery','delivered']))
    <div class="relative ml-4">
        <a href="{{ route('orders.track', $latestOrder->order_number) }}"
           class="text-white text-xl relative">
            🔔
            <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs px-2 py-0.5 rounded-full">
                !
            </span>
        </a>
    </div>
@endif
