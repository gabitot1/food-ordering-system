<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800 tracking-wide">
            Cart
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-100 py-12">
        <div class="max-w-6xl mx-auto px-6">

            @if(session('success'))
                <div class="mb-6 p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                    <p class="text-sm text-gray-700">{{ session('success') }}</p>
                </div>
            @endif

            @if(empty($cart))

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-16 text-center">
                    <div class="text-5xl mb-4">🛒</div>
                    <h3 class="text-lg font-medium text-gray-700">
                        Your cart is empty
                    </h3>
                </div>

            @else

            @php $total = 0; @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

                {{-- LEFT SIDE --}}
                <div class="lg:col-span-2 space-y-5">

                    @foreach($cart as $id => $item)

                        @php
                            $sub = $item['price'] * $item['quantity'];
                            $total += $sub;
                        @endphp

                        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">

                            <div class="flex justify-between items-center">

                                {{-- ITEM --}}
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">
                                        {{ $item['name'] }}
                                    </h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        ₱{{ number_format($item['price'],2) }}
                                    </p>
                                     @if (!empty($item['instruction']))
                                <p class="text-sm text-gray-500 mt-1 italic">
                                    Note: {{ $item['instruction'] }}
                                </p>
                                    
                                @endif
                                </div>

                               

                                {{-- QUANTITY --}}
                                <div class="flex items-center gap-3">

                                    <form action="{{ route('cart.decrease', $id) }}" method="POST">
                                        @csrf
                                        <button class="w-8 h-8 rounded-full border border-gray-300 
                                                       flex items-center justify-center 
                                                       text-gray-600 hover:bg-gray-900 hover:text-white transition">
                                            −
                                        </button>
                                    </form>

                                    <span class="text-sm font-medium w-6 text-center">
                                        {{ $item['quantity'] }}
                                    </span>

                                    <form action="{{ route('cart.increase', $id) }}" method="POST">
                                        @csrf
                                        <button class="w-8 h-8 rounded-full border border-gray-300 
                                                       flex items-center justify-center 
                                                       text-gray-600 hover:bg-gray-900 hover:text-white transition">
                                            +
                                        </button>
                                    </form>

                                </div>

                                {{-- SUBTOTAL --}}
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">
                                        ₱{{ number_format($sub,2) }}
                                    </p>

                                    <form action="{{ route('cart.remove', $id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-red-500 hover:underline mt-1">
                                            Remove
                                        </button>
                                    </form>
                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

                {{-- RIGHT SIDE SUMMARY --}}
                <div class="lg:col-span-1">

                    @php
                        $deliveryFee = 50;
                        $grandTotal = $total + $deliveryFee;
                    @endphp

                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-7 sticky top-24">

                        <h3 class="text-lg font-semibold text-gray-900 mb-5">
                            Order Summary
                        </h3>

                        {{-- PRICE BREAKDOWN --}}
                        <div class="space-y-3 text-sm text-gray-600 mb-6">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span>₱{{ number_format($total,2) }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span>Delivery Fee</span>
                                <span>₱{{ number_format($deliveryFee,2) }}</span>
                            </div>

                            <div class="border-t pt-3 flex justify-between font-semibold text-gray-900">
                                <span>Total</span>
                                <span>₱{{ number_format($grandTotal,2) }}</span>
                            </div>
                        </div>

                        {{-- CHECKOUT FORM --}}
                        <form action="{{ route('checkout.store') }}" method="POST" class="space-y-4">
                            @csrf

                            <input type="text" name="customer_name" placeholder="Full Name"
                                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">

                            <input type="text" name="address" placeholder="Address"
                                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">

                            <input type="email" name="email" placeholder="Email"
                                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">

                            <input type="text" name="contact_number" placeholder="Contact Number"
                                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">

                            <textarea name="instruction" placeholder="Special instructions (optional)"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800"></textarea>

                            <select name="delivery_option"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                                <option value="pickup">Pick-up</option>
                                <option value="delivery">Delivery</option>
                            </select>

                            {{-- PAYMENT METHOD --}}
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-2">
                                    Payment Method
                                </p>

                                <div class="space-y-2">
                                    <label class="flex items-center justify-between border rounded-lg px-3 py-2 cursor-pointer hover:border-gray-900 transition">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="payment_method" value="cash" required>
                                            <span class="text-sm">Cash on Delivery</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center justify-between border rounded-lg px-3 py-2 cursor-pointer hover:border-gray-900 transition">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="payment_method" value="gcash">
                                            <span class="text-sm">GCash</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center justify-between border rounded-lg px-3 py-2 cursor-pointer hover:border-gray-900 transition">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" name="payment_method" value="card">
                                            <span class="text-sm">Credit / Debit Card</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <button type="submit"
                                    class="w-full py-3 bg-gray-900 text-white rounded-xl 
                                           hover:bg-gray-800 transition font-medium">
                                Confirm Order
                            </button>

                        </form>

                    </div>

                </div>

            </div>

            @endif

        </div>
    </div>
</x-app-layout>