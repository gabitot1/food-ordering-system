<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-green-600 tracking-tight">
            Order Details
        </h2>
    </x-slot>

    @php
        $status = $order->status ?? 'pending';
        $paymentStatus = $order->payment_status ?? 'unpaid';
        $statusKey = $status === 'out_of_delivery' ? 'out_for_delivery' : $status;

        $statusClasses = match($status) {
            'pending' => 'bg-amber-100 text-amber-800',
            'preparing' => 'bg-blue-100 text-blue-800',
            'out_for_delivery', 'out_of_delivery' => 'bg-indigo-100 text-indigo-800',
            'delivered', 'completed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-700',
        };

        $paymentClasses = match($paymentStatus) {
            'paid' => 'bg-green-100 text-green-800',
            'refunded' => 'bg-purple-100 text-purple-800',
            default => 'bg-red-100 text-red-700',
        };

        $steps = [
            'pending' => 1,
            'preparing' => 2,
            'out_for_delivery' => 3,
            'delivered' => 4,
        ];
        $currentStep = $steps[$statusKey] ?? 1;
    @endphp

    <div class="min-h-screen bg-gray-50 py-10 px-4">
        <div class="max-w-5xl mx-auto space-y-6">

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 md:p-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            Order No. {{ $order->order_number }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $order->created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 md:justify-end">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $paymentClasses }}">
                            Payment: {{ ucfirst($paymentStatus) }}
                        </span>
                    </div>
                </div>

                <div class="mb-10">
                    <div class="relative">
                        <div class="absolute top-5 left-0 w-full h-1 bg-gray-200 rounded"></div>
                        <div class="absolute top-5 left-0 h-1 bg-green-600 rounded transition-all duration-500"
                             style="width: {{ ($currentStep - 1) * 33.33 }}%"></div>

                        <div class="grid grid-cols-4 md:grid-cols-4 gap-6 relative">
                            @foreach($steps as $key => $value)
                                <div class="text-center">
                                    <div class="mx-auto w-10 h-10 flex items-center justify-center rounded-full text-sm font-semibold
                                        @if($currentStep > $value)
                                            bg-green-600 text-white
                                        @elseif($currentStep == $value)
                                            bg-green-600 text-white
                                        @else
                                            bg-gray-200 text-gray-600
                                        @endif">
                                        @if($currentStep >= $value)
                                            ✓
                                        @else
                                            {{ $value }}
                                        @endif
                                    </div>
                                    <p class="mt-2 text-xs md:text-sm font-medium {{ $currentStep >= $value ? 'text-green-700' : 'text-gray-500' }}">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Customer Name</p>
                        <p class="mt-1 font-semibold text-gray-800">{{ $order->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Contact</p>
                        <p class="mt-1 font-semibold text-gray-800">{{ $order->contact_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Address</p>
                        <p class="mt-1 font-semibold text-gray-800">{{ $order->address }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Delivery Option</p>
                        <p class="mt-1 font-semibold text-gray-800 capitalize">{{ $order->delivery_option }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h4 class="font-semibold text-lg text-gray-900 mb-4">Order Items</h4>

                    <div class="divide-y divide-gray-200">
                        @foreach ($order->items as $item)
                            <div class="py-4 flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-medium text-gray-800">
                                        {{ $item->food->name ?? 'Food Deleted' }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        ₱{{ number_format($item->price, 2) }} x {{ $item->quantity }}
                                    </p>
                                    @if(!empty($item->instruction))
                                        <p class="text-xs text-gray-400 italic mt-2">
                                            Note: {{ $item->instruction }}
                                        </p>
                                    @endif
                                </div>
                                <div class="font-semibold text-gray-900 whitespace-nowrap">
                                    ₱{{ number_format($item->subtotal, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between text-lg font-bold">
                        <span>Total</span>
                        <span class="text-green-700">₱{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                <div class="mt-8 flex flex-col sm:items-center gap-3">
                    <button onclick="openReceipt({{ $order->id }})"
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                        View Receipt
                    </button>

                    @if ($order->status === 'pending')
                        <form action="{{ route('orders.cancel', $order->id) }}"
                              method="POST"
                              data-swal-confirm
                              data-swal-title="Cancel this order?"
                              data-swal-text="Only pending orders can be cancelled."
                              data-swal-confirm-text="Yes, cancel order"
                              data-swal-cancel-text="Keep order"
                              data-swal-icon="warning">
                            @csrf
                            <button class="w-full sm:w-auto px-4 py-2 sm:items-center bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                                Cancel Order
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('orders.index') }}"
                       class="w-full sm:w-auto px-4 py-2 bg-gray-900 sm:items-center  text-white text-sm rounded-lg hover:bg-black text-center transition">
                        Back to Orders
                    </a>
                    
                </div>
            </div>
        </div>
    </div>

    <div id="receiptModal"
         class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[9999] transition-opacity duration-300 opacity-0">
        <div id="receiptBox"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative transform scale-95 translate-y-10 opacity-0 transition-all duration-300 ease-out">
            <button onclick="closeReceiptModal()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-black text-xl">
                ✕
            </button>

            <div id="receiptContent" class="p-6">
                Loading...
            </div>
        </div>
    </div>

    <script>
        function openReceipt(orderId) {
            const modal = document.getElementById('receiptModal');
            const box = document.getElementById('receiptBox');
            const content = document.getElementById('receiptContent');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            setTimeout(() => {
                modal.classList.remove('opacity-0');
                box.classList.remove('scale-95', 'translate-y-10', 'opacity-0');
                box.classList.add('scale-100', 'translate-y-0', 'opacity-100');
            }, 10);

            content.innerHTML = `
                <div class="flex flex-col items-center justify-center py-10">
                    <div class="w-10 h-10 border-4 border-gray-200 border-t-gray-900 rounded-full animate-spin mb-4"></div>
                    <p class="text-sm text-gray-500">Loading receipt...</p>
                </div>
            `;

            fetch(`/orders/${orderId}/receipt-modal`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed');
                    return response.text();
                })
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(() => {
                    content.innerHTML = `
                        <div class="text-center text-red-500 py-10">
                            Failed to load receipt.
                        </div>
                    `;
                });
        }

        function closeReceiptModal() {
            const modal = document.getElementById('receiptModal');
            const box = document.getElementById('receiptBox');

            box.classList.remove('scale-100', 'translate-y-0', 'opacity-100');
            box.classList.add('scale-95', 'translate-y-10', 'opacity-0');
            modal.classList.add('opacity-0');
            document.body.classList.remove('overflow-hidden');

            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        document.getElementById('receiptModal').addEventListener('click', function(e) {
            if (e.target.id === 'receiptModal') {
                closeReceiptModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReceiptModal();
            }
        });
    </script>
</x-app-layout>
