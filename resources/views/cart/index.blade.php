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
                            <form id="checkout-form"
                                action="{{ route('checkout.store') }}"
                                method="POST"
                                class="space-y-4">                   
                                         @csrf

                            <input type="text" name="customer_name" placeholder="Full Name"
                                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                            <p data-error-for="customer_name" class="hidden text-xs text-red-600"></p>

                            <input type="text" name="address" placeholder="Address"
                                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                            <p data-error-for="address" class="hidden text-xs text-red-600"></p>

                            <input type="email" name="email" placeholder="Email"
                                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                            <p data-error-for="email" class="hidden text-xs text-red-600"></p>

                            <input type="text" name="contact_number" placeholder="Contact Number"
                                   required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                            <p data-error-for="contact_number" class="hidden text-xs text-red-600"></p>

                            <textarea name="instruction" placeholder="Special instructions (optional)"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800"></textarea>

                            <select name="delivery_option"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                                <option value="pickup">Pick-up</option>
                                <option value="delivery">Delivery</option>
                            </select>
                            
                            {{-- schedule page --}}
                            <div class="border border-gray-200 rounded-xl p-3 space-y-3">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <input type="checkbox" name="is_scheduled" id="is_scheduled" value="1"
                                        {{ old('is_scheduled') ? 'checked' : '' }}>
                                    Schedule this order
                                </label>

                                <div id="schedule_fields" class="{{ old('is_scheduled') ? '' : 'hidden' }} space-y-2">
                                    <input type="date"
                                        name="scheduled_date"
                                        min="{{ now()->toDateString() }}"
                                        value="{{ old('scheduled_date') }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">

                                    <select name="schedule_slot"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        <option value="">Select time slot</option>
                                        <option value="09:00-11:00" {{ old('schedule_slot') === '09:00-11:00' ? 'selected' : '' }}>09:00 - 11:00</option>
                                        <option value="11:00-13:00" {{ old('schedule_slot') === '11:00-13:00' ? 'selected' : '' }}>11:00 - 13:00</option>
                                        <option value="13:00-15:00" {{ old('schedule_slot') === '13:00-15:00' ? 'selected' : '' }}>13:00 - 15:00</option>
                                        <option value="15:00-17:00" {{ old('schedule_slot') === '15:00-17:00' ? 'selected' : '' }}>15:00 - 17:00</option>
                                    </select>

                                    @error('scheduled_date')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('schedule_slot')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

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
                                <p data-error-for="payment_method" class="hidden text-xs text-red-600 mt-1"></p>
                            </div>

                            <button type="button" id="open-confirm" class="w-full py-2.5 sm:py-3 bg-gray-900 text-white rounded-xl hover:bg-green-600 transition text-sm font-medium">
                                Confirm Order
                            </button>

                        </form>

                        {{-- ORDER CONFIRMATION (receipt style) --}}
                      <div id="order-confirm-modal"
                                class="fixed inset-0 z-50 hidden items-center justify-center">

                                <!-- Overlay -->
                                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

                                <!-- Modal Card -->
                                <div class="relative bg-white rounded-2xl shadow-2xl
                                            w-full max-w-md mx-4 p-6 text-sm text-gray-800">

                                    <!-- Header -->
                                    <div class="text-center mb-4">
                                        <h2 class="text-xl font-bold text-green-600">
                                            Party Tray
                                        </h2>
                                        <p class="text-xs text-gray-400">
                                            Order Confirmation
                                        </p>
                                    </div>

                                    <div class="border-t my-4"></div>

                                    <!-- Order Info -->
                                    <div class="space-y-1 text-sm">
                                        <p><span class="font-medium">Name:</span> <span id="m-name-preview">--</span></p>
                                        <p><span class="font-medium">Address:</span> <span id="m-address">--</span></p>
                                        <p><span class="font-medium">Contact:</span> <span id="m-contact">--</span></p>
                                        <p><span class="font-medium">Delivery:</span> <span id="m-delivery">--</span></p>
                                        <p><span class="font-medium">Payment:</span> <span id="m-payment">--</span></p>
                                    </div>

                                    <div class="border-t my-4"></div>

                                    <!-- Items -->
                                    <div>
                                        <h3 class="font-semibold mb-2 text-gray-700">Items</h3>
                                        <ul id="m-items" class="space-y-2 text-sm"></ul>
                                    </div>

                                    <div class="border-t my-4"></div>

                                    <!-- Total -->
                                    <div class="flex justify-between text-base font-semibold">
                                        <span>Total</span>
                                        <span id="m-total" class="text-green-600">₱0.00</span>
                                    </div>

                                    <!-- Errors -->
                                    <div id="m-errors" class="text-sm text-red-600 mt-3"></div>

                                    <!-- Success -->
                                    <div id="order-success" class="hidden text-center mt-4">
                                        <p class="text-green-600 font-semibold">
                                            Order placed successfully!
                                        </p>
                                        <p class="text-sm mt-1">
                                            Order #: <span id="success-order-no">--</span>
                                        </p>
                                    </div>

                                    <!-- Buttons -->
                                    <div id="modal-actions" class="mt-6 space-y-3">

                                        <button id="cancel-modal"
                                            class="w-full border border-gray-300 py-2 rounded-lg
                                                hover:bg-gray-100 transition">
                                            Back
                                        </button>

                                        <button id="confirm-submit"
                                            class="w-full bg-green-600 text-white py-2 rounded-lg
                                                hover:bg-green-700 transition">
                                            Place Order
                                        </button>

                                    </div>

                                </div>
                            </div>

                        <script>
                            (function(){
                                const cart = @json($cart ?? []);
                                const deliveryFee = {{ $deliveryFee ?? 50 }};

                                function formatPhp(amount){
                                    return '₱' + Number(amount).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                                }

                                const openBtn = document.getElementById('open-confirm');
                                const modal = document.getElementById('order-confirm-modal');
                                const cancelBtn = document.getElementById('cancel-modal');
                                const confirmBtn = document.getElementById('confirm-submit');
                                const form = document.getElementById('checkout-form');

                                function setFieldError(name, message) {
                                    const errorNode = document.querySelector(`[data-error-for="${name}"]`);
                                    const inputNode = form.querySelector(`[name="${name}"]`);
                                    if (errorNode) {
                                        errorNode.textContent = message;
                                        errorNode.classList.remove('hidden');
                                    }
                                    if (inputNode) {
                                        inputNode.classList.add('border-red-500');
                                    }
                                }

                                function clearFieldError(name) {
                                    const errorNode = document.querySelector(`[data-error-for="${name}"]`);
                                    const inputNode = form.querySelector(`[name="${name}"]`);
                                    if (errorNode) {
                                        errorNode.textContent = '';
                                        errorNode.classList.add('hidden');
                                    }
                                    if (inputNode) {
                                        inputNode.classList.remove('border-red-500');
                                    }
                                }

                                function clearAllFieldErrors() {
                                    ['customer_name', 'address', 'email', 'contact_number', 'payment_method'].forEach(clearFieldError);
                                }

                                function validateCheckoutInput(formData) {
                                    const errors = {};
                                    const customerName = String(formData.get('customer_name') || '').trim();
                                    const address = String(formData.get('address') || '').trim();
                                    const email = String(formData.get('email') || '').trim();
                                    const contact = String(formData.get('contact_number') || '').trim();
                                    const payment = String(formData.get('payment_method') || '').trim();

                                    if (!customerName) errors.customer_name = 'Full Name is required.';
                                    else if (customerName.length < 2) errors.customer_name = 'Full Name must be at least 2 characters.';

                                    if (!address) errors.address = 'Address is required.';
                                    else if (address.length < 5) errors.address = 'Address must be at least 5 characters.';

                                    if (!email) {
                                        errors.email = 'Email is required.';
                                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                                        errors.email = 'Email format is invalid.';
                                    }

                                    if (!contact) {
                                        errors.contact_number = 'Contact Number is required.';
                                    } else {
                                        const compactContact = contact.replace(/[^\d+]/g, '');
                                        if (!/^\+?\d{10,15}$/.test(compactContact)) {
                                            errors.contact_number = 'Contact Number must be 10 to 15 digits.';
                                        }
                                    }

                                    if (!payment) errors.payment_method = 'Please select a payment method.';

                                    return errors;
                                }

                                openBtn && openBtn.addEventListener('click', function(){
                                    const formData = new FormData(form);
                                    const validationErrors = validateCheckoutInput(formData);

                                    clearAllFieldErrors();
                                    const errorFields = Object.keys(validationErrors);
                                    if (errorFields.length) {
                                        errorFields.forEach((field) => setFieldError(field, validationErrors[field]));
                                        const firstInput = form.querySelector(`[name="${errorFields[0]}"]`);
                                        firstInput && firstInput.focus();
                                        return;
                                    }

                                    document.getElementById('m-name-preview').textContent = formData.get('customer_name') || '';
                                    document.getElementById('m-address').textContent = formData.get('address') || '';
                                    document.getElementById('m-contact').textContent = formData.get('contact_number') || '';
                                    document.getElementById('m-delivery').textContent = formData.get('delivery_option') || '';
                                    document.getElementById('m-payment').textContent = formData.get('payment_method') || '';

                                    const itemsList = document.getElementById('m-items');
                                    itemsList.innerHTML = '';

                                    let subtotal = 0;
                                    for(const key in cart){
                                        const it = cart[key];
                                        const li = document.createElement('li');
                                        li.textContent = `${it.name} x${it.quantity} — ${formatPhp(it.price * it.quantity)}`;
                                        itemsList.appendChild(li);
                                        subtotal += (it.price * it.quantity);
                                    }

                                    const grand = subtotal + deliveryFee;
                                    document.getElementById('m-total').textContent = formatPhp(grand);

                                    modal.classList.remove('hidden');
                                    modal.classList.add('flex');
                                });

                                cancelBtn && cancelBtn.addEventListener('click', function(){
                                    modal.classList.remove('flex');
                                    modal.classList.add('hidden');
                                });

                                confirmBtn && confirmBtn.addEventListener('click', async function(){
                                    const formData = new FormData(form);
                                    const validationErrors = validateCheckoutInput(formData);

                                    // clear previous errors
                                    const errEl = document.getElementById('m-errors');
                                    errEl.innerHTML = '';
                                    clearAllFieldErrors();

                                    const errorFields = Object.keys(validationErrors);
                                    if (errorFields.length) {
                                        errorFields.forEach((field) => setFieldError(field, validationErrors[field]));
                                        errEl.innerHTML = errorFields.map((field) => `<div>${validationErrors[field]}</div>`).join('');
                                        return;
                                    }

                                    confirmBtn.disabled = true;
                                    confirmBtn.textContent = 'Placing...';
                                    errEl.innerHTML = '';

                                    try {
                                        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                                        const headers = {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json',
                                        };
                                        if (tokenMeta) {
                                            headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
                                        }

                                        const resp = await fetch(form.action, {
                                            method: 'POST',
                                            headers,
                                            body: formData,
                                            credentials: 'same-origin',
                                        });

                                        if (resp.status === 422) {
                                            const data = await resp.json();
                                            const serverErrors = data.errors || {};
                                            const fieldMap = {
                                                customer_name: 'customer_name',
                                                address: 'address',
                                                email: 'email',
                                                contact_number: 'contact_number',
                                                payment_method: 'payment_method',
                                            };

                                            Object.keys(serverErrors).forEach((key) => {
                                                const fieldName = fieldMap[key];
                                                if (!fieldName) return;
                                                const first = (serverErrors[key] || [])[0];
                                                if (first) setFieldError(fieldName, first);
                                            });

                                            errEl.innerHTML = Object.values(serverErrors).flat().map((msg) => `<div>${msg}</div>`).join('');
                                            return;
                                        }

                                        if (!resp.ok) {
                                            errEl.textContent = 'Something went wrong. Please try again.';
                                            return;
                                        }

                                        const data = await resp.json();

                                        if (!data.success) {
                                            errEl.textContent = 'Something went wrong. Please try again.';
                                            return;
                                        }

                                        modal.classList.remove('flex');
                                        modal.classList.add('hidden');

                                        if (window.Swal) {
                                            const successOptions = (typeof getSwalBaseOptions === 'function')
                                                ? getSwalBaseOptions({
                                                    icon: 'success',
                                                    title: 'Order placed successfully',
                                                    text: 'Order #' + (data.order_number || ''),
                                                    confirmButtonColor: '#16a34a',
                                                    confirmButtonText: 'Track Order',
                                                    allowOutsideClick: false,
                                                })
                                                : {
                                                    icon: 'success',
                                                    title: 'Order placed successfully',
                                                    text: 'Order #' + (data.order_number || ''),
                                                    confirmButtonColor: '#16a34a',
                                                    confirmButtonText: 'Track Order',
                                                    allowOutsideClick: false,
                                                };
                                            await Swal.fire(successOptions);
                                        }

                                        if (data.track_url) {
                                            window.location.href = data.track_url;
                                            return;
                                        }

                                        window.location.reload();
                                    } catch (e) {
                                        errEl.textContent = 'Network error. Please try again.';
                                    } finally {
                                        confirmBtn.disabled = false;
                                        confirmBtn.textContent = 'Place Order';
                                    }
                                });

                                form?.addEventListener('input', function (event) {
                                    const fieldName = event.target?.getAttribute?.('name');
                                    if (!fieldName) return;
                                    clearFieldError(fieldName);
                                });

                                form?.addEventListener('change', function (event) {
                                    const fieldName = event.target?.getAttribute?.('name');
                                    if (!fieldName) return;
                                    clearFieldError(fieldName);
                                });
                            })();
                            document.addEventListener('DOMContentLoaded', function () {
                                const cb = document.getElementById('is_scheduled');
                                const fields = document.getElementById('schedule_fields');
                                if (!cb || !fields) return;

                                function toggleSchedule() {
                                    fields.classList.toggle('hidden', !cb.checked);
                                }

                                cb.addEventListener('change', toggleSchedule);
                                toggleSchedule();
                            });
                        </script>

                    </div>

                </div>

            </div>

            @endif

        </div>
    </div>
</x-app-layout>
