<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-green-600 tracking-tight">
            Admin Foods
        </h2>
    </x-slot>

    <div class="py-12 bg-white min-h-screen px-4">
        <div class="max-w-7xl mx-auto space-y-8">

            {{-- SUCCESS MESSAGE --}}
            {{-- @if (session('success'))
                <div class="p-4 rounded-2xl bg-green-500/20 backdrop-blur-lg border border-green-400 text-green-100 shadow-lg animate-fade-in">
                    {{ session('success') }}
                </div>
            @endif --}}

            {{-- FILTER --}}
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-6 border border-white/40 transition-all duration-500 hover:shadow-green-400/40">
                <form method="GET" action="{{ route('admin.foods.index') }}"
                      class="flex flex-col sm:flex-row gap-3 items-center">

                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Search food..."
                           class="border border-gray-300 rounded-xl px-4 py-2 text-sm w-full sm:w-64 focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition-all duration-300 shadow-sm hover:shadow-md">

                    <select name="availability"
                            class="border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 shadow-sm hover:shadow-md w-full sm:w-auto">
                        <option value="">All</option>
                        <option value="available" {{ request('availability')=='available' ? 'selected' : '' }}>
                            Available
                        </option>
                        <option value="unavailable" {{ request('availability')=='unavailable' ? 'selected' : '' }}>
                            Unavailable
                        </option>
                    </select>

                    <button class="px-5 py-2 bg-gradient-to-r from-green-600 to-emerald-500 text-white text-sm rounded-xl shadow-lg hover:from-green-700 hover:to-emerald-600 hover:shadow-green-300 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105">
                        Search
                    </button>

                    <a href="{{ route('admin.foods.index') }}"
                       class="px-5 py-2 text-sm border border-gray-300 rounded-xl bg-white text-gray-600 hover:bg-green-50 transition-all duration-300">
                        Clear
                    </a>
                </form>
            </div>

            {{-- TABLE CARD --}}
            <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl p-6 border border-white/40 overflow-hidden">

                <div class="flex justify-between items-center mb-5">
                    <p class="text-sm text-gray-600">
                        Total foods:
                        <span class="font-semibold">{{ $foods->total() }}</span>
                    </p>

                    <a href="{{ route('admin.foods.create') }}"
                       class="px-5 py-2 bg-gradient-to-r from-green-600 to-emerald-500 text-white text-sm rounded-xl shadow-lg hover:from-green-700 hover:to-emerald-600 hover:shadow-green-300 transition-all duration-300 transform hover:-translate-y-1 hover:scale-105">
                        + Add Food
                    </a>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-gray-200/70 bg-white/70">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/80 text-gray-600">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Image</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Price</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($foods as $food)
                                <tr class="border-b border-gray-100 hover:bg-green-50/40 transition-all duration-300">

                                    {{-- IMAGE --}}
                                    <td class="px-4 py-3">
                                        @if ($food->image)
                                            <img src="{{ asset('storage/' .$food->image) }}"
                                                 class="w-14 h-14 object-cover rounded-xl ring-1 ring-gray-200 shadow-sm">
                                        @else
                                            <div class="w-14 h-14 bg-gray-100 rounded-xl ring-1 ring-gray-200"></div>
                                        @endif
                                    </td>

                                    {{-- NAME --}}
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800">
                                        {{ $food->name }}
                                        </div>
                                    </td>

                                    {{-- CATEGORY --}}
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                            {{ $food->category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>

                                    {{-- PRICE --}}
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-gray-900">₱{{ number_format($food->price, 2) }}</span>
                                    </td>

                                    {{-- TOGGLE --}}
                                    <td class="px-4 py-3">
                                        <form action="{{ route('admin.foods.toggle', $food->id) }}" method="POST" class="flex items-center gap-3">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition duration-300
                                                {{ $food->is_available ? 'bg-green-600' : 'bg-gray-300' }}">
                                                <span class="inline-block h-5 w-5 transform rounded-full bg-white transition duration-300
                                                {{ $food->is_available ? 'translate-x-5' : 'translate-x-1' }}">
                                                </span>
                                            </button>
                                            <span class="text-xs font-medium {{ $food->is_available ? 'text-green-700' : 'text-gray-500' }}">
                                                {{ $food->is_available ? 'Available' : 'Unavailable' }}
                                            </span>
                                        </form>
                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">

                                            <a href="{{ route('admin.foods.edit',$food->id) }}"
                                               class="px-3 py-1.5 text-xs font-medium bg-yellow-500 text-white rounded-lg shadow-sm hover:bg-yellow-600 transition-all duration-300">
                                                Edit
                                            </a>

                                            <form action="{{ route('admin.foods.destroy', $food->id) }}"
                                                  method="POST"
                                                  data-swal-confirm
                                                  data-swal-title="Delete food item?"
                                                  data-swal-text="This food item will be permanently removed."
                                                  data-swal-confirm-text="Yes, delete"
                                                  data-swal-cancel-text="Cancel"
                                                  data-swal-icon="warning">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded-lg shadow-sm hover:bg-red-700 transition-all duration-300">
                                                    Delete
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="text-5xl mb-3">📂</div>
                                        <p class="text-gray-500 text-base">No foods found.</p>
                                        <p class="text-xs text-gray-400 mt-1">Try a different search or add a new food item.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                 <div class="mt-10"> {{ $foods->links('vendor.pagination.custom') }} </div>

            </div>
        </div>
    </div>
</x-app-layout>
