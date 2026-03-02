<x-app-layout>
    <x-slot name="header">
 <h2 class="text-2xl font-semibold text-green-800 tracking-wide">
                Foods
        </h2>
    </x-slot>

    {{-- FULL PAGE BACKGROUND --}}
    <div class="min-h-screen py-10" style="background-color:#3a5a40;">
        <div class="max-w-7xl mx-auto px-6">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-white text-[#3a5a40] rounded-xl shadow-md animate-fadeIn">
                    {{ session('success') }}
                </div>
            @endif

            {{-- FOODS GRID --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                @forelse($foods as $food)

                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden
                                hover:shadow-2xl hover:-translate-y-2
                                transition-all duration-300 flex flex-col">

                        {{-- IMAGE --}}
                        @if ($food->image)
                            <img src="{{ asset('storage/'.$food->image) }}"
                                 class="h-48 w-full object-cover
                                        hover:scale-105 transition duration-500">
                        @else
                            <div class="h-48 bg-gray-200"></div>
                        @endif

                        {{-- CONTENT --}}
                        <div class="p-5 flex flex-col justify-between flex-1">

                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">
                                    {{ $food->name }}
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    ₱{{ number_format($food->price, 2) }}
                                </p>

                                @if(!$food->is_available)
                                    <span class="inline-block mt-2 text-xs bg-red-100 text-red-600 px-2 py-1 rounded-md">
                                        Not Available
                                    </span>
                                @endif
                            </div>

                            {{-- BUTTON SECTION --}}
                            <div class="mt-5">

                                @if ($food->is_available)
                                    <form action="{{ route('cart.add') }}" method="POST"
                                          class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="food_id" value="{{ $food->id }}">

                                        <input type="number"
                                               name="qty"
                                               value="1"
                                               min="1"
                                               class="w-14 border border-gray-300 rounded-md px-2 py-1 text-sm
                                                      focus:ring-2 focus:ring-[#588157] focus:outline-none">

                                        <button type="submit"
                                            class="flex-1 py-2 text-sm font-medium text-white
                                                   bg-[#588157] rounded-lg
                                                   hover:bg-[#344e41]
                                                   active:scale-95
                                                   transition duration-200">
                                            Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <button
                                        class="w-full py-2 text-sm bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                                        Unavailable
                                    </button>
                                @endif

                            </div>

                        </div>
                    </div>

                @empty
                    <div class="col-span-3 text-center text-white text-lg">
                        No foods available.
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
