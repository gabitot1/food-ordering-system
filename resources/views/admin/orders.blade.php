<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-green-600 tracking-tight">
            Admin Orders
        </h2>
    </x-slot>

    <div class="min-h-screen bg-white py-12 px-4">
        <div class="max-w-6xl mx-auto space-y-8">

      
            <form method="GET"
                  action="{{ route('admin.orders') }}"
                  class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/40 transition-all duration-500 hover:shadow-green-400/40">

                <div class="flex flex-col lg:flex-row gap-4 lg:items-end">

                    {{-- SEARCH --}}
                    <div class="flex-1">
                        <label class=" px-3 py-2 text-xs text-gray-500 uppercase tracking-wide">
                            Search
                        </label>
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"

                               class="mt-1 w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition-all duration-300 shadow-sm hover:shadow-md">
                    </div>

                    <div class="flex justify-center gap-2">
                        <label class="text-xs text-gray-500 uppercase tracking-wide">
                         
                        </label>
                        <input type="date"
                               name="date_from"
                               value="{{ request('date_from') }}"
                               class="mt-1 border border-gray-300 rounded-xl px-1 py-1 sm:px-4 sm:py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 shadow-sm hover:shadow-md">
                 
                        <label class="mt-1 mx-auto text-xs text-gray-500 uppercase tracking-wide ">
                            To
                        </label>
                        <input type="date"
                               name="date_to"
                               value="{{ request('date_to') }}"
                               class="mt-1 border border-gray-300 rounded-xl px-1 py-1 sm:px-4 sm:py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 shadow-sm hover:shadow-md">
                    </div>

                    {{-- BUTTONS --}}
                    <div class="flex justify-center gap-3">

                        <button type="submit"
                                class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-500 text-white text-sm rounded-xl shadow-lg hover:from-green-700 hover:to-emerald-600 hover:shadow-green-300 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105">
                            Apply
                        </button>

                        <a href="{{ route('admin.orders.export.pdf', request()->query()) }}"
                           class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-xl shadow hover:bg-indigo-700 hover:scale-105 transition-all duration-300">
                            Export PDF
                        </a>

                    </div>

                </div>
            </form>


         
            @php
                $current = request('status');
                $tabs = [
                    '' => 'All',
                    'pending' => 'Pending',
                    'preparing' => 'Preparing',
                    'out_of_delivery' => 'Out of Delivery',
                    'delivered' => 'Delivered',
                ];
            @endphp

            <div class="flex gap-3 flex-wrap items-center justify-center">

                @foreach($tabs as $key => $label)
                    <a href="{{ route('admin.orders', array_merge(request()->query(), ['status' => $key ?: null])) }}"
                       class="px-4 py-2 text-xs  rounded-full transition-all duration-300
                       {{ ($current === $key)
                           ? 'bg-green-600 text-white shadow'
                           : 'bg-white border border-gray-200 text-gray-600 hover:bg-green-50' }}">
                        {{ $label }}
                    </a>
                @endforeach

            </div>


            {{-- ===================== --}}
            {{-- ORDERS LIST --}}
            {{-- ===================== --}}
            @if(!$orders || $orders->count() === 0)

                <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/40 p-16 text-center">
                    <div class="text-6xl mb-4">📂</div>
                    <p class="text-gray-500 text-lg">
                        No orders found.
                    </p>
                    <p class="text-sm text-gray-400 mt-2">
                        Try changing filters or search criteria.
                    </p>
                </div>

            @else

                <div class=" bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/40 overflow-hidden divide-y">

                    @foreach($orders as $order)

                        @php
                            $status = $order->status ?? 'pending';
                            $pay = $order->payment_status ?? 'unpaid';

                            $statusColor = match($status) {
                                'pending' => 'text-yellow-600 bg-yellow-50',
                                'preparing' => 'text-blue-600 bg-blue-50',
                                'out_of_delivery' => 'text-purple-600 bg-purple-50',
                                'delivered' => 'text-green-600 bg-green-50',
                                default => 'text-gray-600 bg-gray-50',
                            };

                            $payColor = match($pay) {
                                'unpaid' => 'text-gray-600 bg-gray-100',
                                'paid' => 'text-green-600 bg-green-50',
                                'refunded' => 'text-purple-600 bg-purple-50',
                                default => 'text-gray-600 bg-gray-100',
                            };
                        @endphp

                        <div class="p-6 hover:bg-green-50/60 transition-all duration-300 group">

                            <div class="flex flex-col md:flex-row md:justify-between gap-6">

                                {{-- ORDER INFO --}}
                                <div class="space-y-2">

                                    <div class="text-sm font-semibold text-green-600">
                                        {{ $order->order_number ?? ('ORD-'.$order->id) }}
                                    </div>

                                    <div class="text-sm text-gray-600">
                                        {{ $order->customer_name ?? 'Guest Customer' }}
                                    </div>

                                    <div class="text-sm text-gray-500">
                                        ₱{{ number_format($order->total ?? 0, 2) }}
                                        • {{ $order->items?->count() ?? 0 }} items
                                    </div>

                                </div>

                                {{-- BADGES --}}
                                <div class="flex items-center gap-3 flex-wrap">

                                    <span class="px-3 py-1 text-xs rounded-full {{ $statusColor }}">
                                        {{ ucfirst(str_replace('_',' ',$status)) }}
                                    </span>

                                    <span class="px-3 py-1 text-xs rounded-full {{ $payColor }}">
                                        {{ ucfirst($pay) }}
                                    </span>

                                    <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                                        {{ ucfirst($order->payment_method ?? 'cash') }}
                                    </span>

                                </div>
                            </div>


                            {{-- UPDATE --}}
                            <form action="{{ route('admin.orders.update', $order->id) }}"
                                  method="POST"
                                  class="mt-6">
                                @csrf
                                @method('PATCH')

                                <div class="grid md:grid-cols-3 gap-4">

                                    <select name="status"
                                            class="border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 shadow-sm hover:shadow-md">
                                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="preparing" {{ $status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                        <option value="out_of_delivery" {{ $status === 'out_of_delivery' ? 'selected' : '' }}>Out of Delivery</option>
                                        <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    </select>

                                    <select name="payment_status"
                                            class="border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 shadow-sm hover:shadow-md">
                                        <option value="unpaid" {{ $pay === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                        <option value="paid" {{ $pay === 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="refunded" {{ $pay === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                    </select>

                                    <button type="submit"
                                            class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-500 text-white text-sm rounded-xl shadow-lg hover:from-green-700 hover:to-emerald-600 hover:shadow-green-300 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105">
                                        Update
                                    </button>

                                </div>
                            </form>

                        </div>

                    @endforeach

                </div>

            @endif


            {{-- PAGINATION --}}
             <div class="mt-10 bg-white p-3 sm:p-4 rounded-2xl  overflow-x-auto"> {{ $orders->links('vendor.pagination.custom') }} </div>

        </div>
    </div>
</x-app-layout>
