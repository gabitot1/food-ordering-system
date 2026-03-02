<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-green-600 tracking-tight">
            Admin Categories
        </h2>
    </x-slot>

    <div class="min-h-screen bg-white py-12 px-4">

        <div class="max-w-5xl mx-auto space-y-8">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-green-500/20 backdrop-blur-lg 
                            border border-green-400 text-green-100 
                            shadow-lg animate-fade-in">
                    {{ session('success') }}
                </div>
            @endif


            {{-- ===================== --}}
            {{-- ADD CATEGORY CARD --}}
            {{-- ===================== --}}
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl 
                        p-8 border border-white/40 transition-all 
                        duration-500 hover:shadow-green-400/40">

                <h3 class="text-lg font-semibold text-gray-700 mb-6">
                    Add New Category
                </h3>

                <form method="POST"
                      action="{{ route('admin.categories.store') }}"
                      class="flex flex-col sm:flex-row gap-4">
                    @csrf

                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="Enter category name..."
                           class="flex-1 px-5 py-3 rounded-xl border border-gray-300
                                  focus:ring-2 focus:ring-green-500 
                                  focus:border-green-500 outline-none 
                                  transition-all duration-300 shadow-sm 
                                  hover:shadow-md"
                           required>

                    <button type="submit"
                            class="px-6 py-3 rounded-xl text-white font-semibold
                                   bg-gradient-to-r from-green-600 to-emerald-500
                                   hover:from-green-700 hover:to-emerald-600
                                   shadow-lg hover:shadow-green-300
                                   transition-all duration-300
                                   transform hover:-translate-y-1 hover:scale-105">
                        + Add
                    </button>
                </form>

                @error('name')
                    <p class="mt-3 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>


            {{-- ===================== --}}
            {{-- CATEGORY LIST CARD --}}
            {{-- ===================== --}}
            <div class="bg-white/90 backdrop-blur-lg rounded-3xl 
                        shadow-2xl border border-white/40 overflow-hidden">

                <div class="px-8 py-5 border-b bg-white/60 backdrop-blur">
                    <h3 class="text-lg font-semibold text-gray-700">
                        Category List
                    </h3>
                </div>

                @forelse($categories as $category)

                    <div class="flex items-center justify-between 
                                px-8 py-5 border-b hover:bg-green-50/60 
                                transition-all duration-300 group">

                        <div>
                            <div class="font-semibold text-gray-800 text-lg">
                                {{ $category->name ?? '-' }}
                            </div>

                            <div class="text-sm text-gray-500 mt-1">
                                {{ $category->foods_count }} items
                            </div>
                        </div>

                        <div class="flex gap-3">

                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                               class="px-4 py-2 text-sm font-medium 
                                      bg-yellow-500 text-white rounded-xl
                                      shadow hover:bg-yellow-600
                                      hover:scale-105 transition-all duration-300">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('admin.categories.destroy', $category->id) }}"
                                  data-swal-confirm
                                  data-swal-title="Delete category?"
                                  data-swal-text="This category will be removed."
                                  data-swal-confirm-text="Yes, delete"
                                  data-swal-cancel-text="Cancel"
                                  data-swal-icon="warning">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="px-4 py-2 text-sm font-medium 
                                               bg-red-600 text-white rounded-xl
                                               shadow hover:bg-red-700
                                               hover:scale-105 transition-all duration-300">
                                    Delete
                                </button>
                            </form>

                        </div>
                    </div>

                @empty
                    <div class="py-16 text-center">
                        <div class="text-6xl mb-4">📂</div>
                        <p class="text-gray-500 text-lg">
                            No categories yet.
                        </p>
                        <p class="text-sm text-gray-400 mt-2">
                            Add your first category above.
                        </p>
                    </div>
                @endforelse

            </div>

        </div>
    </div>
</x-app-layout>
