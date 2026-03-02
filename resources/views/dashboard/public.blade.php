<x-app-layout>

<x-slot name="header">
    <h2 class="text-2xl font-bold text-gray-900">
        Dashboard
    </h2>
</x-slot>

<div class="max-w-7xl mx-auto py-10 px-4">

    <!-- HERO CARD -->
    <div class="bg-gradient-to-r from-green-500 to-green-600
                rounded-3xl p-10 text-white shadow-xl mb-12">

        <div class="text-3xl font-bold mb-2">
            Ready to eat? 🍽️
        </div>

        <div class="text-sm opacity-90 mb-6">
            Your favorites are waiting.
        </div>

        <a href="{{ route('home') }}"
           class="bg-white text-green-600 px-6 py-3 rounded-2xl font-semibold
                  shadow-md hover:scale-105 transition duration-300 inline-block">
            Order Now
        </a>

    </div>



    <!-- STATS (Food App Style Pills) -->
    <div class="flex gap-6 mb-12 flex-wrap">

        <div class="bg-white rounded-2xl px-8 py-5 shadow-md flex items-center gap-4">
            <div class="text-3xl font-bold text-gray-900">
                {{ $totalOrders }}
            </div>
            <div class="text-sm text-gray-400 uppercase">
                Orders
            </div>
        </div>

        <div class="bg-white rounded-2xl px-8 py-5 shadow-md flex items-center gap-4">
            <div class="text-3xl font-bold text-yellow-500">
                {{ $pendingOrders }}
            </div>
            <div class="text-sm text-gray-400 uppercase">
                Pending
            </div>
        </div>

        <div class="bg-white rounded-2xl px-8 py-5 shadow-md flex items-center gap-4">
            <div class="text-3xl font-bold text-green-600">
                {{ $completedOrders }}
            </div>
            <div class="text-sm text-gray-400 uppercase">
                Completed
            </div>
        </div>

    </div>



    <!-- RECENT ORDERS -->
    <div class="mb-14">

        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            Your Orders
        </h3>

        <div class="space-y-4">

            @forelse($orders->take(4) as $order)

                <div class="bg-white rounded-2xl shadow-md p-6 flex justify-between items-center hover:shadow-xl transition">

                    <div>
                        <div class="font-semibold text-gray-900">
                            {{ $order->order_number }}
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            {{ $order->created_at->format('M d') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">

                        <span class="text-xs px-4 py-1 rounded-full
                            {{ $order->status == 'pending'
                                ? 'bg-yellow-100 text-yellow-700'
                                : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($order->status) }}
                        </span>

                        @if($order->order_number)
                            <a href="{{ route('orders.track', $order->order_number) }}"
                               class="text-green-600 font-semibold hover:scale-110 transition">
                                →
                            </a>
                        @endif

                    </div>

                </div>

            @empty
                <div class="text-gray-400 text-sm">
                    No orders yet.
                </div>
            @endforelse

        </div>

    </div>



    <!-- BEST SELLERS (Big Images Like Foodpanda) -->
    <div>

        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            Best Sellers
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">

            @foreach(\App\Models\Foods::where('is_available', 1)->inRandomOrder()->take(4)->get() as $best)

                <div class="group bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition">

                    <div class="relative overflow-hidden">
                        <img src="{{ asset('storage/' . $best->image) }}"
                             class="w-full h-52 object-cover group-hover:scale-110 transition duration-700">
                    </div>

                    <div class="p-6">

                        <div class="font-semibold text-gray-900 group-hover:text-green-600 transition">
                            {{ $best->name }}
                        </div>

                        <div class="text-sm text-gray-400 mt-2">
                            ₱{{ number_format($best->price,2) }}
                        </div>

                        <a href="{{ route('home') }}"
                           class="mt-4 inline-block text-green-600 font-semibold hover:underline">
                            Order →
                        </a>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>

</x-app-layout>