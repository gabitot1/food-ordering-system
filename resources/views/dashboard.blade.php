<x-app-layout>

    <x-slot  name="header">
        <h2 class="text-3xl font-bold text-gray-800 tracking-tight">
            Menu
        </h2>
        <style>
            html{
                scroll-behavior: smooth;
            }
        </style>
    </x-slot>

    <div id="menu" class="max-w-7xl mx-auto py-8 sm:py-12 px-4 sm:px-6">

        {{-- CATEGORY FILTER --}}
        <div class="flex gap-3 mb-8 overflow-x-auto pb-2 scrollbar-hide">
            <a href="{{ route('home') }}"
               class="px-5 py-2 rounded-full text-sm font-medium transition
               {{ request('category') ? 'bg-gray-200 text-gray-700' : 'bg-green-600 text-white shadow-lg' }}">
                All
            </a>

            @foreach($categories as $category)
                <a href="?category={{ $category->id }}"
                   class="px-5 py-2 rounded-full text-sm font-medium transition
                   {{ request('category') == $category->id 
                        ? 'bg-green-600 text-white shadow-lg' 
                        : 'bg-gray-200 text-gray-700 hover:bg-green-600 hover:text-white' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        {{-- EMPTY STATE --}}
        @if($foods->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <p class="text-lg">No foods available in this category.</p>
            </div>
        @else

        {{-- FOOD GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">

            @foreach($foods as $food)
                <div class="group bg-white rounded-3xl shadow-md hover:shadow-2xl transition duration-500 overflow-hidden">

                    <div class="relative overflow-hidden">
                        <img src="{{ asset('storage/' . $food->image) }}"
                             class="w-full h-52 object-cover group-hover:scale-105 transition duration-500">

                        @if($food->is_available)
                            <span class="absolute top-4 left-4 bg-green-600 text-white text-xs px-3 py-1 rounded-full shadow">
                                Available
                            </span>
                        @endif
                    </div>

                    <div class="p-2 sm:p-6">
                        <h3 class="font-semibold text-lg text-gray-800 group-hover:text-green-600 transition">
                            {{ $food->name }}
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 min-h-[40px]">
                            {{ Str::limit($food->description, 70) }}
                        </p>

                      <button
    class="detailsBtn mt-3 w-full bg-gray-100 text-gray-700 py-2 rounded-xl hover:bg-gray-200 transition text-sm"
    data-id="{{ $food->id }}"
    data-name="{{ $food->name }}"
    data-price="{{ $food->price }}"
    data-image="{{ asset('storage/' . $food->image) }}"
    data-description="{{ $food->description }}">
    Details
</button>

                        <div class="flex justify-between items-center mt-5">

                            <span class="text-green-700 font-bold text-xl">
                                ₱{{ number_format($food->price, 2) }}
                            </span>

                            {{-- ADD TO CART --}}
                            <form action="{{ route('cart.add') }}"
                                  method="POST"
                                  class="global-add-to-cart">
                                @csrf
                                <input type="hidden" name="food_id" value="{{ $food->id }}">
                                <input type="hidden" name="quantity" value="1">

                                <button type="submit"
                                        class="cartBtn bg-green-600 text-white px-4 py-2 rounded-xl text-sm sm:text-base hover:bg-green-700 transition shadow-md">
                                    Add
                                </button>

                                <div class="cart-loading hidden flex items-center gap-2 text-xs text-gray-500 mt-2">
                                    <div class="mini-spinner"></div>
                                    Adding...
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            @endforeach

        </div>
        @endif
    </div>

    {{-- FOOD MODAL --}}
    <div id="foodModal"
         class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[10000] opacity-0 transition">

        <div id="foodModalBox"
             class="bg-white w-full max-w-lg max-h-[93vh] sm:-[85%] md:w-full max-w-sm sm:max-w-md md:max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl shadow-2xl p-5 sm:p-6 transform scale-95 translate-y-10 opacity-0 transition-all duration-300">

            <button onclick="closeFoodModal()"
                    class="absolute top-4 right-4 text-gray-500 hover:text-black text-xl">
                ✕
            </button>

            <img id="modalImage"
                 class="w-full h-60 object-cover rounded-xl mb-4">

            <h3 id="modalName" class="text-xl font-bold mb-2"></h3>
            <p id="modalDescription" class="text-sm text-gray-500 mb-4"></p>

            <label class="font-semibold text-sm">Size</label>
            <select id="foodSize"
                    class="w-full border rounded-lg p-2 mt-2 mb-4"
                    onchange="updateTotal()">
                <option value="0">Regular</option>
                <option value="20">Large</option>
                <option value="40">Extra Large</option>
            </select>

            <div class="flex justify-between items-center mb-4">
                <span class="font-semibold">Quantity</span>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="changeQty(-1)" class="bg-gray-200 px-4 py-2 rounded text-lg ">-</button>
                    <span id="foodQty">1</span>
                    <button type="button" onclick="changeQty(1)" class="bg-gray-200 px-4 py-2 rounded text-lg">+</button>
                </div>
            </div>

            <div class="flex justify-between items-center mb-4">
                <span class="font-bold">Total</span>
                <span id="foodTotal" class="text-green-700 font-bold"></span>
            </div>

            <form action="{{ route('cart.add') }}"
                  method="POST"
                  class="add-to-cart-form">
                @csrf
                <input type="hidden" name="food_id" id="modalFoodId">
                <input type="hidden" name="quantity" id="modalQuantity">
                <input type="hidden" name="size_price" id="modalSizePrice">

                <textarea name="instruction"
                          placeholder="Special instruction (optional)"
                          class="w-full border rounded-lg p-2 text-sm mb-2"></textarea>

                <button type="submit"
                        class="w-full bg-green-600 text-white py-2 rounded-xl hover:bg-green-800 transition">
                    Add to Cart
                </button>
            </form>
        </div>
    </div>

    {{-- TOAST --}}
    <div id="cartToast"
     class="fixed inset-0 flex items-center justify-center
            opacity-0 pointer-events-none
            transition-all duration-300 z-[11000]">

    <div class="bg-white px-6 py-5 sm:py-6 rounded-2xl shadow-2xl
                w-[90%] sm:w-auto sm:max-w-md flex flex-col items-center gap-3
                transform scale-95 transition-all duration-300">

        <div class="text-3xl">🛒</div>
        <p class="text-lg font-semibold text-gray-800">
            Added to Cart
        </p>
        <p class="text-sm text-gray-500">
            Your item was added successfully
        </p>

    </div>
</div>

    {{-- LOADING OVERLAY --}}
    <div id="loadingOverlay"
         class="fixed inset-0 bg-black/40 flex items-center justify-center hidden z-40">
        <div class="bg-white p-8 rounded-2xl shadow-xl flex flex-col items-center gap-4">
            <div class="loader"></div>
            <p class="text-sm text-gray-600 font-medium">
                Adding to cart...
            </p>
        </div>
    </div>
    <a href="#menu" class="fixed bottom-6 right-6 bg-green-600 text-white p-4  rounded-full shadow-lg hover:bg-green-700 transition">↑</a>
    {{-- <section id="menu"> --}}
</x-app-layout>
<script>
let basePrice = 0;
let quantity = 1;

function openFoodModal(id, name, price, image, description) {

    const modal = document.getElementById('foodModal');
    const box = document.getElementById('foodModalBox');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => {
        modal.classList.remove('opacity-0');
        box.classList.remove('scale-95','translate-y-10','opacity-0');
    },10);

    document.getElementById('modalName').innerText = name;
    document.getElementById('modalDescription').innerText = description;
    document.getElementById('modalImage').src = image;
    document.getElementById('modalFoodId').value = id;

    basePrice = price;
    quantity = 1;
    document.getElementById('foodQty').innerText = quantity;

    updateTotal();
}

function closeFoodModal() {
    const modal = document.getElementById('foodModal');
    modal.classList.add('hidden');
}

function changeQty(amount){
    quantity += amount;
    if(quantity < 1) quantity = 1;
    document.getElementById('foodQty').innerText = quantity;
    updateTotal();
}

function updateTotal(){
    let sizePrice = parseFloat(document.getElementById('foodSize').value);
    let total = (basePrice + sizePrice) * quantity;
    document.getElementById('foodTotal').innerText = '₱' + total.toFixed(2);
    document.getElementById('modalQuantity').value = quantity;
    document.getElementById('modalSizePrice').value = sizePrice;
}

// GLOBAL AJAX HANDLER
if (!window.__dashboardCartSubmitBound) {
window.__dashboardCartSubmitBound = true;
document.addEventListener('submit', function(e){

    if (
        e.target.classList.contains('add-to-cart-form') ||
        e.target.classList.contains('global-add-to-cart')
    ) {

        e.preventDefault();
        e.stopImmediatePropagation();
        if (e.target.dataset.submitting === '1') return;
        e.target.dataset.submitting = '1';

        const overlay = document.getElementById('loadingOverlay');
        const toast = document.getElementById('cartToast');

        overlay.classList.remove('hidden');

        fetch(e.target.action,{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'X-Requested-With':'XMLHttpRequest'
            },
            body:new FormData(e.target)
        })
        .then(res=>res.json())
        .then(() => {

    overlay.classList.add('hidden');

    const box = toast.querySelector('div');
    toast.classList.remove('opacity-0');
    box.classList.remove('scale-95');
    box.classList.add('scale-100');

    setTimeout(() => {
        toast.classList.add('opacity-0');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
    }, 2000);

    closeFoodModal();
})
        .catch(()=>{
            overlay.classList.add('hidden');
            if (window.Swal) {
                const options = (typeof getSwalBaseOptions === 'function')
                    ? getSwalBaseOptions({
                        icon: 'error',
                        title: 'Request failed',
                        text: 'Something went wrong.',
                        confirmButtonColor: '#16a34a'
                    })
                    : {
                        icon: 'error',
                        title: 'Request failed',
                        text: 'Something went wrong.',
                        confirmButtonColor: '#16a34a'
                    };
                Swal.fire(options);
            }
        })
        .finally(() => {
            e.target.dataset.submitting = '0';
        });
    }
}, true);
}
document.addEventListener('click', function(e) {

    if (!e.target.classList.contains('detailsBtn')) return;

    const btn = e.target;

    const id = btn.dataset.id;
    const name = btn.dataset.name;
    const price = parseFloat(btn.dataset.price);
    const image = btn.dataset.image;
    const description = btn.dataset.description;

    openFoodModal(id, name, price, image, description);
});
</script>
