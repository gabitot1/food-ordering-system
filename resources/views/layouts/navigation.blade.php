<nav x-data="{ open: false, scrolled: false }"
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
     :class="scrolled 
        ? 'bg-white/80 backdrop-blur-md shadow-lg py-2' 
        : 'bg-[#3a5a40] py-4'"
     class="sticky top-0 z-[9999] relative transition-all duration-500 ease-in-out">

    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-3">

            <img src="{{ asset('images/logo.png') }}"
            class="h-10 transition-all duration-300"
            :class="scrolled ? 'invert' : 'invert-0'">
                

            <span class="text-lg font-bold tracking-wide"
                :class="scrolled ? 'text-green-800' : 'text-white'">
                Party Tray
                </span>

        </a>
        <div class="hidden sm:flex sm:items-center sm:space-x-4">

    <!-- Other nav links -->

    
  

        <div class="hidden md:flex space-x-8 font-medium">
            <a href="{{ route('public.dashboard') }}"
            class="nav-link"
            :class="scrolled ? 'text-gray-700' : 'text-white'">
                Dashboard
            </a>
            <a href="{{ route('home') }}"
               class="nav-link"
               :class="scrolled ? 'text-gray-700' : 'text-white'">
                Menu
            </a>

            <a href="{{ route('cart.index') }}"
               class="nav-link"
               :class="scrolled ? 'text-gray-700' : 'text-white'">
                Cart
            </a>

            <a href="{{ route('orders.index') }}"
               class="nav-link"
               :class="scrolled ? 'text-gray-700' : 'text-white'">
                Orders
            </a>
@auth
@if(auth()->user()->is_admin == 1)

<div x-data="{ adminOpen: false }" class="relative">

    <!-- Admin Button -->
    <button @click="adminOpen = !adminOpen"
        class="nav-link flex items-center gap-1"
        :class="scrolled ? 'text-gray-700' : 'text-white'">
        Admin ▾
    </button>

    <!-- Dropdown -->
    <div x-show="adminOpen"
         @click.away="adminOpen = false"
         x-transition
         class="absolute right-0 mt-3 w-52 bg-white rounded-xl shadow-xl border py-2 z-50">

        <a href="{{ route('admin.dashboard') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
            Dashboard
        </a>

        <a href="{{ route('admin.categories.index') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
            Categories
        </a>

        <a href="{{ route('admin.orders') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
            Orders
        </a>

        <a href="{{ route('admin.foods.index') }}"
           class="block px-4 py-2 text-sm hover:bg-gray-100">
            Foods
        </a>

        <hr class="my-2">

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full text-left px-4 py-2 text-sm hover:bg-red-100 text-red-600">
                Logout
            </button>
        </form>

    </div>
</div>

@endif
@endauth
            {{-- {{ auth()->check() ? 'Logged In' : 'Not Logged In' }}
            {{ auth()->user()->id }}
            {{ auth()->user()->role }} --}}
            @php
    $orderIds = session()->get('my_orders', []);
    $latestOrder = null;

    if(!empty($orderIds)){
        $latestOrder = \App\Models\Orders::whereIn('id', $orderIds)
                        ->latest()
                        ->first();
    }
@endphp

{{-- @if($latestOrder)
<div class="relative ml-4">
    <a href="{{ route('orders.track', $latestOrder->order_number) }}"
       class="text-gray-800 text-xl relative">
        🔔

        @if(in_array($latestOrder->status, ['cooking','ready']))
            <span class="absolute -top-1 -right-2 bg-red-600 text-white text-xs px-2 py-0.5 rounded-full">
                !
            </span>
        @endif
    </a>
</div>
@endif --}}
       @php
    $orderIds = session()->get('my_orders', []);
    $orders = collect();

    if(!empty($orderIds)){
        $orders = \App\Models\Orders::whereIn('id', $orderIds)
                    ->latest()
                    ->get();
    }

    $activeOrders = $orders->whereIn('status', ['cooking','ready'])->count();
@endphp

@if($orders->count() > 0)
<div class="relative ml-4" id="notificationWrapper">

    <!-- 🔔 Bell -->
    <button onclick="toggleNotif()" 
        class="relative text-gray-700 text-xl hover:text-black transition">
    🔔

    <span id="notifBadge"
          class="absolute -top-1 -right-2 bg-red-600 text-white 
                 text-xs px-2 py-0.5 rounded-full hidden">
        <span id="notifCount">0</span>
    </span>
</button>

    <!-- 📦 Dropdown -->
    <div id="notifDropdown"
         class="hidden absolute right-0 mt-3 w-80 bg-white 
                rounded-xl shadow-xl border p-4 z-50 max-h-96 overflow-y-auto">

        <h3 class="font-semibold text-sm mb-3">
            My Orders
        </h3>

        @foreach($orders as $order)
            <div class="border-b py-2 last:border-0">
                <p class="text-sm font-medium">
                    #{{ $order->order_number }}
                </p>

                <p class="text-xs 
                    @if($order->status == 'pending') text-yellow-600
                    @elseif($order->status == 'cooking') text-blue-600
                    @elseif($order->status == 'ready') text-purple-600
                    @elseif($order->status == 'completed') text-green-600
                    @elseif($order->status == 'cancelled') text-red-600
                    @endif">
                    {{ ucfirst($order->status) }}
                </p>

                <a href="{{ route('orders.track', $order->order_number) }}"
                   class="text-xs text-green-600 hover:underline">
                    View →
                </a>
            </div>
        @endforeach

        <div class="mt-3 text-center">
            <a href="{{ route('orders.index') }}"
               class="text-sm text-blue-600 hover:underline">
                View All Orders
            </a>
        </div>
    </div>
</div>
@endif

        <!-- Mobile Button -->
        <button @click="open = !open"
                class="md:hidden text-white"
                :class="scrolled ? 'text-gray-800' : 'text-white'">
            ☰
        </button>

    </div>

</nav>
<script>
function toggleNotif() {
    document.getElementById('notifDropdown')
        .classList.toggle('hidden');
}

// Close when clicking outside
document.addEventListener('click', function(event) {
    const wrapper = document.getElementById('notificationWrapper');
    const dropdown = document.getElementById('notifDropdown');

    if (wrapper && !wrapper.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
