<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Order Success
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow rounded">
                <p class="text-green-700 font-semibold">
                    {{ session('success') ?? 'Checkout Success!' }}
                </p>

                <div class="mt-4 flex gap-3">
                    <a href="{{ route('admin.foods.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Back to Foods
                    </a>

                    <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded">
                        View Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
