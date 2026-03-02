<x-app-layout>
    <div id="disableGlobalFlash" class="hidden"></div>
    <x-slot name="header">
      <h2 class="text-2xl font-semibold text-green-800 tracking-wide">
            My Orders
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-6xl mx-auto px-6">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))
                <div class="mb-8 p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-700">{{ session('success') }}</p>
                </div>
            @endif

            <div class="mb-6 bg-white p-5 rounded-2xl shadow-sm border border-gray-200">

                    <form method="GET" action="{{ route('orders.index') }}" class="flex gap-3">

                        <input type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search order number, name, or status..."
                            class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm
                                    focus:ring-2 focus:ring-gray-900 focus:outline-none">

                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white rounded-xl
                                    hover:bg-green-800 transition">
                            Search
                        </button>

                        @if(request('search'))
                            <a href="{{ route('orders.index') }}"
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm
                                    hover:bg-gray-100 transition">
                                Reset
                            </a>
                        @endif

                    </form>

                </div>

            {{-- ORDERS LIST --}}
            <div class="space-y-6">

                @forelse($orders as $order)

                @php
                    $normalizedStatus = match($order->status) {
                        'out_of_delivery' => 'out_for_delivery',
                        'delivered', 'completed' => 'completed',
                        default => $order->status,
                    };

                    $statusColor = match($normalizedStatus) {
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'preparing' => 'bg-blue-100 text-blue-800',
                        'out_for_delivery' => 'bg-indigo-100 text-indigo-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-700'
                    };

                    $paymentColor = $order->payment_status == 'paid'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800';

                    $progressWidth = match($normalizedStatus) {
                        'pending' => '25%',
                        'preparing' => '50%',
                        'out_for_delivery' => '75%',
                        'completed' => '100%',
                        'cancelled' => '100%',
                        default => '25%'
                    };

                    $progressColor = $normalizedStatus === 'cancelled'
                        ? 'bg-red-500'
                        : 'bg-green-500';
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition duration-300">

                    {{-- TOP ROW --}}
                    <div class="flex justify-between items-start flex-wrap gap-4">

                        {{-- LEFT --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Order #{{ $order->order_number }}
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                {{ $order->created_at->format('M d, Y • h:i A') }}
                            </p>

                            <p class="mt-3 text-sm text-gray-600">
                                {{ $order->items->count() }} items
                            </p>
                        </div>

                        {{-- RIGHT --}}
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-xl font-semibold text-gray-900">
                                ₱{{ number_format($order->total,2) }}
                            </p>
                        </div>

                    </div>

                    {{-- PROGRESS BAR --}}
                    <div class="mt-6">
                        <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                            <div class="h-full {{ $progressColor }} transition-all duration-700"
                                 style="width: {{ $progressWidth }}">
                            </div>
                        </div>
                    </div>

                    {{-- BADGES ROW --}}
                    <div class="mt-5 flex justify-between items-center flex-wrap gap-3">

                        <div class="flex gap-3">

                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ ucfirst(str_replace('_',' ', $normalizedStatus)) }}
                            </span>

                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $paymentColor }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>

                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                {{ ucfirst($order->payment_method) }}
                            </span>

                        </div>

                        <a href="{{ route('orders.show', $order->id) }}"
                           class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition">
                            View Details
                        </a>

                    </div>

                </div>

                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
                        <p class="text-gray-500 text-sm">
                            You haven’t placed any orders yet.
                        </p>
                    </div>
                    
                @endforelse
                    
            </div>

           <div class="mt-10 bg-white p-4 rounded-2xl shadow-sm border border-green-8"> {{ $orders->links() }} </div>

        </div>

    </div>
</x-app-layout>
