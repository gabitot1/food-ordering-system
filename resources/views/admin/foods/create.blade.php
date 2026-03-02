<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="text-2xl font-bold text-green-600 tracking-tight">
            Add Food
        </h2>
    </x-slot> --}}

    <div class="min-h-screen bg-gray-100 py-12 px-4">
        <div class="max-w-2xl mx-auto bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/40 p-8">

            {{-- ERRORS --}}
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.foods.store') }}"
                  enctype="multipart/form-data"
                  class="space-y-6">
                @csrf

                {{-- CATEGORY --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Category
                    </label>
                    <select name="category_id"
                            required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                   focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none
                                   transition-all duration-300 shadow-sm hover:shadow-md">

                        <option value="">Select Category</option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- FOOD NAME --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Food Name
                    </label>
                    <input name="name"
                           required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                  focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none
                                  transition-all duration-300 shadow-sm hover:shadow-md"
                           placeholder="Enter food name">
                </div>

                {{-- PRICE --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Price
                    </label>
                    <input type="number"
                           name="price"
                           step="0.01"
                           required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                                  focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none
                                  transition-all duration-300 shadow-sm hover:shadow-md"
                           placeholder="0.00">
                </div>

                {{-- IMAGE --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Image
                    </label>

                    <input type="file"
                           name="image"
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5
                                  focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none
                                  transition-all duration-300 shadow-sm hover:shadow-md">

                  
                </div>

                {{-- BUTTONS --}}
                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin.foods.index') }}"
                       class="px-5 py-2 text-sm border border-gray-300 rounded-xl bg-white text-gray-600 hover:bg-green-50 transition-all duration-300">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-6 py-2 text-sm bg-gradient-to-r from-green-600 to-emerald-500 text-white rounded-xl
                                   hover:from-green-700 hover:to-emerald-600 transition-all duration-300 shadow-lg hover:shadow-green-300 transform hover:-translate-y-1 hover:scale-105">
                        Save
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
