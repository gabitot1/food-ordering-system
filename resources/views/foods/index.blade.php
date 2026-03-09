<x-app-layout>
    <x-slot name="header">
 <h2 class="text-lg sm:text-2xl font-semibold text-green-800 tracking-wide">
                Foods
        </h2>
    </x-slot>

    {{-- FULL PAGE BACKGROUND --}}
    <div class="min-h-screen py-4 sm:py-10" style="background-color:#3a5a40;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))
                <div class="mb-4 sm:mb-6 flex justify-center">
                    <div class="w-full max-w-xs sm:max-w-sm px-4 py-3 bg-white text-[#3a5a40] text-sm rounded-xl shadow-md text-center animate-fadeIn">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            {{-- FOODS GRID --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-8">

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
                        <div class="p-3 sm:p-5 flex flex-col justify-between flex-1">

                            <div>
                                <h3 class="text-sm sm:text-lg font-semibold text-gray-800 break-words">
                                    {{ $food->name }}
                                </h3>

                                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                    ₱{{ number_format($food->price, 2) }}
                                </p>

                                @if(!$food->is_available)
                                    <span class="inline-block mt-2 text-[11px] sm:text-xs bg-red-100 text-red-600 px-2 py-1 rounded-md">
                                        Not Available
                                    </span>
                                @endif
                            </div>

                            {{-- BUTTON SECTION --}}
                            <div class="mt-5">
                                @auth
                                    @if (auth()->user()->is_admin)
                                        <form action="{{ route('admin.foods.toggle', $food->id) }}" method="POST" class="mb-3">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="w-full py-2 text-xs sm:text-sm font-medium text-white rounded-lg transition duration-200 {{ $food->is_available ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                                                {{ $food->is_available ? 'Set Unavailable' : 'Set Available' }}
                                            </button>
                                        </form>
                                    @endif
                                @endauth

                                @if ($food->is_available)
                                    <form action="{{ route('cart.add') }}" method="POST"
                                          class="flex flex-col md:flex-row md:items-center gap-3">
                                        @csrf
                                        <input type="hidden" name="food_id" value="{{ $food->id }}">

                                        <input type="number"
                                               name="qty"
                                               value="1"
                                               min="1"
                                               class="w-full md:w-16 border border-gray-300 rounded-md px-3 py-1.5 text-xs sm:text-sm
                                                      focus:ring-2 focus:ring-[#588157] focus:outline-none">

                                        <button type="submit"
                                            class="w-full md:flex-1 py-2 text-xs sm:text-sm font-medium text-white
                                                   bg-[#588157] rounded-lg
                                                   hover:bg-[#344e41]
                                                   active:scale-95
                                                   transition duration-200">
                                            Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <button
                                        class="w-full py-2 text-xs sm:text-sm bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                                        Unavailable
                                    </button>
                                @endif

                            </div>

                        </div>
                    </div>

                @empty
                    <div class="col-span-full text-center text-white text-lg">
                        No foods available.
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
