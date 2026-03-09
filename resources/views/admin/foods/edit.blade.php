<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="text-2xl font-bold text-green-600 tracking-tight">
            Edit Food
        </h2>
    </x-slot> --}}

    <div class="min-h-screen bg-gray-100 py-12 px-4">
        <div class="max-w-2xl mx-auto bg-white/90 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/40 p-8">

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.foods.update', $food->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category_id"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition-all duration-300 shadow-sm hover:shadow-md">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $food->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input name="name"
                           value="{{ old('name', $food->name) }}"
                           required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition-all duration-300 shadow-sm hover:shadow-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                    <input type="number"
                           step="0.01"
                           name="price"
                           value="{{ old('price', $food->price) }}"
                           required
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition-all duration-300 shadow-sm hover:shadow-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>

                    @if ($food->image)
                        <img src="{{ asset('storage/' . $food->image) }}"
                             class="w-20 h-20 object-cover rounded-xl ring-1 ring-gray-200 shadow-sm mb-3">
                    @endif

                    <input type="file"
                           name="image"
                           class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none transition-all duration-300 shadow-sm hover:shadow-md">
                    <p class="text-xs text-gray-500 mt-1">Leave empty if you do not want to change the image.</p>
                </div>

                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/60 px-4 py-3">
                    <input type="checkbox"
                           name="is_available"
                           value="1"
                           class="w-5 h-5 text-green-600 rounded focus:ring-green-500"
                           {{ old('is_available', $food->is_available ?? 1) ? 'checked' : '' }}>
                    <label class="text-sm font-medium text-gray-700">Available</label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('admin.foods.index') }}"
                       class="px-5 py-2 text-sm border border-gray-300 rounded-xl bg-white text-gray-600 hover:bg-green-50 transition-all duration-300">
                        Back
                    </a>

                    <button type="submit"
                            class="px-5 py-2 text-sm bg-gradient-to-r from-green-600 to-emerald-500 text-white rounded-xl hover:from-green-700 hover:to-emerald-600 transition-all duration-300 shadow-lg hover:shadow-green-300 transform hover:-translate-y-1 hover:scale-105">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
