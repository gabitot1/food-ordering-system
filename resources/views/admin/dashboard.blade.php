<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-green-600 tracking-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="min-h-screen bg-white py-12 px-4">

        <div class="max-w-7xl mx-auto space-y-8">

            {{-- ===================== --}}
            {{-- MAIN STAT CARDS --}}
            {{-- ===================== --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white/90 backdrop-blur-lg rounded-3xl p-6 shadow-2xl border border-white/40 transition-all duration-300 hover:shadow-green-400/30">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">
                        Total Orders
                    </div>
                    <div class="text-3xl font-bold mt-4 text-gray-900">
                        {{ $totalOrders }}
                    </div>
                </div>

                <div class="bg-white/90 backdrop-blur-lg rounded-3xl p-6 shadow-2xl border border-white/40 transition-all duration-300 hover:shadow-green-400/30">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">
                        Total Sales
                    </div>
                    <div class="text-3xl font-bold mt-4 text-green-600">
                        ₱{{ number_format($totalSales,2) }}
                    </div>
                </div>

                <div class="bg-white/90 backdrop-blur-lg rounded-3xl p-6 shadow-2xl border border-white/40 transition-all duration-300 hover:shadow-green-400/30">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">
                        Today Orders
                    </div>
                    <div class="text-3xl font-bold mt-4 text-gray-900">
                        {{ $todayOrders }}
                    </div>
                </div>

                <div class="bg-white/90 backdrop-blur-lg rounded-3xl p-6 shadow-2xl border border-white/40 transition-all duration-300 hover:shadow-green-400/30">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">
                        Today Sales
                    </div>
                    <div class="text-3xl font-bold mt-4 text-indigo-600">
                        ₱{{ number_format($todaySales,2) }}
                    </div>
                </div>

            </div>


            {{-- ===================== --}}
            {{-- STATUS CARDS --}}
            {{-- ===================== --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="bg-white/90 backdrop-blur-lg text-gray-800 rounded-3xl p-7 shadow-2xl border border-white/40">
                    <div class="text-xs uppercase tracking-wide opacity-80">
                        Pending Orders
                    </div>
                    <div class="text-4xl font-bold mt-4 text-yellow-600">
                        {{ $pendingCount }}
                    </div>
                </div>

                <div class="bg-white/90 backdrop-blur-lg text-gray-800 rounded-3xl p-7 shadow-2xl border border-white/40">
                    <div class="text-xs uppercase tracking-wide opacity-80">
                        Preparing Orders
                    </div>
                    <div class="text-4xl font-bold mt-4 text-blue-600">
                        {{ $preparingCount }}
                    </div>
                </div>

            </div>


            {{-- ===================== --}}
            {{-- CHARTS --}}
            {{-- ===================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="bg-white/90 backdrop-blur-lg rounded-3xl p-7 shadow-2xl border border-white/40">
                    <h3 class="text-lg font-semibold text-gray-700 mb-6">
                        Sales (Last 7 Days)
                    </h3>
                    <canvas id="salesChart" height="110"></canvas>
                </div>

                <div class="bg-white/90 backdrop-blur-lg rounded-3xl p-7 shadow-2xl border border-white/40">
                    <h3 class="text-lg font-semibold text-gray-700 mb-6">
                        Monthly Sales (Last 6 Months)
                    </h3>
                    <canvas id="monthlySalesChart" height="110"></canvas>
                </div>

            </div>


            {{-- ===================== --}}
            {{-- BEST SELLERS --}}
            {{-- ===================== --}}
            <div class="bg-white/90 backdrop-blur-lg rounded-3xl p-7 shadow-2xl border border-white/40">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">
                        Best Sellers
                    </h3>
                    <a href="{{ route('admin.foods.index') }}"
                       class="text-sm text-green-600 hover:underline">
                        Manage Foods →
                    </a>
                </div>

                @if(!$topFoods || $topFoods->count() === 0)
                    <div class="text-center py-12 text-gray-400">
                        No data available.
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($topFoods as $row)

                        <div class="flex justify-between items-center">

                            <div>
                                <div class="text-base font-semibold text-gray-800">
                                    {{ $row->food->name ?? 'Food Deleted' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Total Sold: {{ $row->total_qty }}
                                </div>
                            </div>

                            <div class="w-48 bg-gray-100 rounded-full h-3">
                                <div class="bg-indigo-600 h-3 rounded-full"
                                     style="width: {{ min($row->total_qty * 5, 100) }}%">
                                </div>
                            </div>

                        </div>

                        @endforeach
                    </div>
                @endif

            </div>

            {{-- ===================== --}}
            {{-- DATE FILTER --}}
            {{-- ===================== --}}
            <div class="bg-white/90 backdrop-blur-lg rounded-3xl p-7 shadow-2xl border border-white/40 mb-8">
                <form method="GET" action="{{ route('admin.orders.export.pdf') }}">
                    <div class="flex items-center space-x-4">
                        <label for="date" class="text-gray-700 font-semibold">Filter by Date:</label>
                        <input type="date" id="date" name="date" class="border border-gray-300 rounded-lg px-4 py-2">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            Export PDF
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: @json($salesDays),
                datasets: [{
                    data: @json($salesTotals),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.08)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    data: @json($monthlySales),
                    backgroundColor: '#4f46e5'
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });
    </script>

</x-app-layout>
