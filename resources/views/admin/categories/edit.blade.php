<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-green-600 tracking-tight">
            Edit Category
        </h2>
    </x-slot>

    <div class="flex justify-center items-center min-h-[70vh] px-4">

        <div class="w-full max-w-xl bg-white/70 backdrop-blur-lg 
                    shadow-2xl rounded-3xl border border-gray-200 
                    p-10 transition-all duration-500 hover:shadow-green-200">

            <!-- Title -->
            <div class="mb-8 text-center">
                <h3 class="text-xl font-semibold text-gray-700">
                    Update Category Details
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Modify the category name below.
                </p>
            </div>

            <form method="POST" 
                  action="{{ route('admin.categories.update', $category->id) }}"
                  class="space-y-6">

                @csrf
                @method('PATCH')

                <!-- Input -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-600 mb-2">
                        Category Name
                    </label>

                    <input type="text"
                           name="name"
                           value="{{ $category->name }}"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 
                                  focus:ring-2 focus:ring-green-500 
                                  focus:border-green-500 
                                  outline-none transition-all duration-300
                                  shadow-sm hover:shadow-md">
                </div>

                <!-- Buttons -->
                <div class="flex justify-between items-center pt-4">

                    <a href="{{ route('admin.categories.index') }}"
                       class="text-gray-500 hover:text-gray-700 transition">
                        ← Cancel
                    </a>

                    <button type="submit"
                            class="px-6 py-3 rounded-xl text-white font-semibold
                                   bg-gradient-to-r from-green-600 to-emerald-500
                                   hover:from-green-700 hover:to-emerald-600
                                   shadow-lg hover:shadow-green-300
                                   transition-all duration-300 
                                   transform hover:-translate-y-1 hover:scale-105">
                        Update Category
                    </button>

                </div>

            </form>

        </div>

    </div>
</x-app-layout>