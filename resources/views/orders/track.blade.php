<x-app-layout>
    <div id="disableGlobalFlash" class="hidden"></div>
    <x-slot name="header">
        <h2 class="text-lg sm:text-2xl font-semibold text-green-800">
            Order Tracking
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-4 sm:py-10 px-4 sm:px-6">

        <div class="bg-white shadow rounded-2xl p-3 sm:p-6">

            <h3 class="text-sm sm:text-lg font-bold mb-3 sm:mb-4 break-all sm:break-normal">
                Order #: {{ $order->order_number }}
            </h3>

<div class="space-y-2 sm:space-y-3 text-xs sm:text-base text-gray-700">

    <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
        <span class="font-medium">Name</span>
        <span class="sm:text-right break-words">{{ $order->customer_name }}</span>
    </div>

    <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
        <span class="font-medium">Total</span>
        <span class="font-semibold text-green-700 sm:text-right">
            ₱{{ number_format($order->total,2) }}
        </span>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center">
        <span class="font-medium">Status</span>
        <span class="px-3 py-1 text-[11px] sm:text-xs rounded-full
            {{ $order->payment_status === 'paid'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700' }} w-fit">
            {{ ucfirst($order->payment_status) }}
        </span>
    </div>

    <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
        <span class="font-medium">Delivery</span>
        <span class="sm:text-right">{{ ucfirst($order->delivery_option) }}</span>
    </div>

    <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
        <span class="font-medium">Date</span>
        <span class="sm:text-right">{{ $order->created_at->format('M d, Y h:i A') }}</span>
    </div>

</div>
           <div class="mt-4">
            <a href="{{ route('orders.index') }}"
            class="inline-flex w-full sm:w-auto justify-center mt-4 sm:mt-6 bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm rounded-xl transition ">
                Back to Orders
            </a>
        </div>
        </div>

    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (!window.Swal) return;
                const message = @json(session('success'));
                const options = (typeof getSwalBaseOptions === 'function')
                    ? getSwalBaseOptions({
                        icon: 'success',
                        title: 'Order placed successfully',
                        text: message,
                        confirmButtonColor: '#16a34a'
                    })
                    : {
                        icon: 'success',
                        title: 'Order placed successfully',
                        text: message,
                        confirmButtonColor: '#16a34a'
                    };
                Swal.fire(options);
            });
        </script>
    @endif
</x-app-layout>
