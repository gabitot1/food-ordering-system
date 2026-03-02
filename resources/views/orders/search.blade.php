<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-green-800">
            Track Your Order
        </h2>
    </x-slot>

    <div class="max-w-md mx-auto py-10">

        <div class="bg-white p-6 rounded-xl shadow">

            <form action="{{ route('orders.search') }}" method="POST">
                @csrf

                <input type="text"
                       name="order_number"
                       placeholder="Enter Order Number"
                       class="w-full border rounded-lg p-2 mb-4"
                       required>

                <button type="submit"
                        class="w-full bg-green-600 text-white py-2 rounded-lg">
                    Search Order
                </button>
            </form>

            @if(session('error'))
                <p class="text-red-600 mt-3">
                    {{ session('error') }}
                </p>
            @endif

        </div>

    </div>
</x-app-layout>
