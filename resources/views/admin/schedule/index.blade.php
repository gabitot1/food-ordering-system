<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Schedule</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-6 px-4">
        <form method="GET" class="mb-6">
            <input type="date" name="date" value="{{ $date }}" class="border rounded px-3 py-2">
            <button class="bg-green-600 text-white px-4 py-2 rounded">Filter</button>
        </form>

        @forelse($grouped as $slot => $orders)
            <div class="bg-white rounded-xl shadow p-4 mb-4">
                <h3 class="font-semibold mb-3">Slot: {{ $slot }}</h3>
                <div class="space-y-2">
                    @foreach($orders as $order)
                        <div class="border rounded p-3 flex justify-between">
                            <div>
                                <p class="font-medium">#{{ $order->order_number }}</p>
                                <p class="text-sm text-gray-600">{{ $order->customer_name }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm mb-1">{{ ucfirst(str_replace('_',' ', $order->status)) }}</div>
                                <a href="{{ route('admin.orders', ['search' => $order->order_number]) }}"
                                   class="text-xs text-blue-600 hover:underline">
                                    View Order
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow p-4">No scheduled orders for this date.</div>
        @endforelse
    </div>
</x-app-layout>
