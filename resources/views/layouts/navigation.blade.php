<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<nav x-data="{ open: false, scrolled: false, adminOpen: false, mobileAdminOpen: false }"
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
     :class="scrolled 
        ? 'bg-white/80 backdrop-blur-md shadow-lg py-2' 
        : 'bg-[#3a5a40] py-4'"
     class="sticky top-0 z-[9999] transition-all duration-500 ease-in-out">

    <!-- TOP BAR -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex justify-between items-center gap-3">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-3 min-w-0">
            <img src="{{ asset('images/logo.png') }}"
                 class="h-10 w-10 shrink-0 transition-all duration-300"
                 :class="scrolled ? 'invert' : 'invert-0'">

            <span class="truncate text-base sm:text-lg font-bold tracking-wide"
                  :class="scrolled ? 'text-green-800' : 'text-white'">
                Party Tray
            </span>
        </a>

        <!-- DESKTOP MENU -->
        <div class="hidden md:flex items-center space-x-8 font-medium">

            <a href="{{ route('public.dashboard') }}"
               :class="scrolled ? 'text-gray-700' : 'text-white'"
               class="hover:text-green-600 transition">
                Dashboard
            </a>

            <a href="{{ route('home') }}"
               :class="scrolled ? 'text-gray-700' : 'text-white'"
               class="hover:text-green-600 transition">
                Menu
            </a>

            <a href="{{ route('cart.index') }}"
               :class="scrolled ? 'text-gray-700' : 'text-white'"
               class="hover:text-green-600 transition">
                Cart
            </a>

            <a href="{{ route('orders.index') }}"
               :class="scrolled ? 'text-gray-700' : 'text-white'"
               class="hover:text-green-600 transition">
                Orders
            </a>

            @auth
                @if(auth()->user()->is_admin == 1)
                <div class="relative">

                    <button @click="adminOpen = !adminOpen"
                            :class="scrolled ? 'text-gray-700' : 'text-white'"
                            class="flex items-center gap-1 hover:text-green-600 transition">
                        Admin ▾
                    </button>

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
                        <a href="{{ route('admin.schedule') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">
                            Schedule
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
        </div>

        <div class="flex shrink-0 items-center gap-1.5 sm:gap-3">
            @auth
                @if(auth()->user()->is_admin == 1)
                <div class="relative flex items-center" id="adminNotificationWrapper">
                    <button onclick="toggleAdminNotif()"
                            type="button"
                            class="relative inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full border border-white/20 text-base sm:text-lg transition hover:bg-white/10"
                            :class="scrolled ? 'text-gray-700 border-gray-300 hover:bg-gray-100' : 'text-white'">
                        <span>🔔</span>
                        <span id="adminNotifBadge"
                              class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] min-w-[16px] h-4 px-1 rounded-full hidden items-center justify-center">
                            <span id="adminNotifCount">0</span>
                        </span>
                    </button>

                    <div id="adminNotifDropdown"
                         class="hidden absolute right-0 top-full mt-2 w-72 max-w-[calc(100vw-1rem)] bg-white rounded-xl shadow-xl border p-3 sm:p-4 z-50 max-h-[70vh] overflow-y-auto">

                        <h3 class="font-semibold text-xs sm:text-sm mb-2 sm:mb-3">Admin Notifications</h3>
                        <div id="adminNotifList" class="space-y-2 text-xs sm:text-sm text-gray-700"></div>

                        <div class="mt-3 text-center">
                            <a href="{{ route('admin.orders') }}"
                               class="text-xs sm:text-sm text-blue-600 hover:underline">
                                View All Orders
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            @endauth

            @if($navOrders->count() > 0)
            <div class="relative flex items-center" id="notificationWrapper">
                <button onclick="toggleNotif()"
                        type="button"
                        class="relative inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full border border-white/20 text-base sm:text-lg transition hover:bg-white/10"
                        :class="scrolled ? 'text-gray-700 border-gray-300 hover:bg-gray-100' : 'text-white'">
                    <span>🔔</span>
                    <span id="notifBadge"
                          class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] min-w-[16px] h-4 px-1 rounded-full hidden items-center justify-center">
                        <span id="notifCount">0</span>
                    </span>
                </button>

                <div id="notifDropdown"
                     class="hidden absolute right-0 top-full mt-2 w-64 sm:w-72 max-w-[calc(100vw-1rem)] bg-white rounded-xl shadow-xl border p-3 sm:p-4 z-50 max-h-[70vh] overflow-y-auto">

                    <h3 class="font-semibold text-xs sm:text-sm mb-2 sm:mb-3">
                        My Orders
                    </h3>

                    @foreach($navOrders as $order)
                        <div class="border-b py-2 last:border-0">
                            <p class="text-xs sm:text-sm font-medium break-all">
                                #{{ $order->order_number }}
                            </p>

                            <p data-order-status="{{ $order->id }}"
                               class="text-[11px] sm:text-xs 
                                    @if($order->status == 'pending') text-yellow-600
                                    @elseif($order->status == 'preparing') text-blue-600
                                    @elseif($order->status == 'out_of_delivery') text-purple-600
                                    @elseif($order->status == 'delivered' || $order->status == 'completed') text-green-600
                                    @elseif($order->status == 'cancelled') text-red-600
                                    @else text-gray-600
                                    @endif">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </p>

                            <a href="{{ route('orders.track', $order->order_number) }}"
                               class="text-[11px] sm:text-xs text-green-600 hover:underline">
                                View →
                            </a>
                        </div>
                    @endforeach

                    <div class="mt-3 text-center">
                        <a href="{{ route('orders.index') }}"
                           class="text-xs sm:text-sm text-blue-600 hover:underline">
                            View All Orders
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- MOBILE HAMBURGER -->
            <button id="mobileToggle" @click="open = !open"
                    class="md:hidden inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-full border border-white/20 text-xl sm:text-2xl"
                    :class="scrolled ? 'text-gray-800 border-gray-300' : 'text-white'"
                    aria-label="Open menu">
                ☰
            </button>
        </div>
    </div>

    <!-- MOBILE SIDEBAR -->
    <div id="mobileMenuWrapper"
         x-cloak
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="md:hidden fixed inset-0 z-50">

           <!-- Overlay -->
           <div id="mobileOverlay"
               x-cloak
               x-show="open"
               @click="open = false"
               class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        <!-- Panel -->
         <aside id="mobileMenu"
             x-cloak
             x-show="open"
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             @click.away="open = false"
             class="absolute right-0 top-0 h-full w-[85%] max-w-xs bg-white shadow-2xl p-4 space-y-4 overflow-y-auto">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" class="h-9 w-9">
                    <span class="font-bold text-lg">Party Tray</span>
                </div>
                <button @click="open = false" class="text-xl p-2 rounded hover:bg-gray-100">✕</button>
            </div>

            <nav class="mt-2 flex flex-col gap-2">
                <a href="{{ route('public.dashboard') }}" @click="open = false"
                   class="block rounded-lg px-3 py-3 text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">Dashboard</a>

                <a href="{{ route('home') }}" @click="open = false"
                   class="block rounded-lg px-3 py-3 text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">Menu</a>

                <a href="{{ route('cart.index') }}" @click="open = false"
                   class="block rounded-lg px-3 py-3 text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">Cart</a>

                <a href="{{ route('orders.index') }}" @click="open = false"
                   class="block rounded-lg px-3 py-3 text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">Orders</a>
            </nav>

            @auth
                @if(auth()->user()->is_admin == 1)
                <div class="border-t pt-4">
                    <button @click="mobileAdminOpen = !mobileAdminOpen" type="button"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-3 text-left text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">
                        <span>Admin</span>
                        <span x-text="mobileAdminOpen ? '−' : '+'"></span>
                    </button>
                    <div x-show="mobileAdminOpen" class="mt-2 space-y-2">
                        <a href="{{ route('admin.orders') }}" class="block px-3 py-2 rounded hover:bg-gray-50 text-sm text-gray-600">View Orders</a>
                    </div>
                </div>
                @endif
            @endauth

        </aside>
    </div>

</nav>
<script>
function toggleNotif() {
    document.getElementById('notifDropdown')
        .classList.toggle('hidden');
}

function toggleAdminNotif() {
    const dropdown = document.getElementById('adminNotifDropdown');
    if (!dropdown) return;

    dropdown.classList.toggle('hidden');

    if (!dropdown.classList.contains('hidden')) {
        markAdminNotificationsRead();
    }
}

async function fetchAdminNotifications() {
    const listNode = document.getElementById('adminNotifList');
    const badgeNode = document.getElementById('adminNotifBadge');
    const countNode = document.getElementById('adminNotifCount');
    if (!listNode || !badgeNode || !countNode) return;

    try {
        const response = await fetch("{{ route('admin.notifications') }}", {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) return;

        const data = await response.json();
        const notifications = Array.isArray(data.notifications) ? data.notifications : [];
        const unreadCount = Number(data.unread_count || 0);

        if (unreadCount > 0) {
            badgeNode.classList.remove('hidden');
            badgeNode.classList.add('inline-flex');
            countNode.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
        } else {
            badgeNode.classList.add('hidden');
            badgeNode.classList.remove('inline-flex');
            countNode.textContent = '0';
        }

        if (notifications.length === 0) {
            listNode.innerHTML = '<p class="text-gray-500">No recent orders.</p>';
            return;
        }

        listNode.innerHTML = notifications.map((row) => {
            const typeLabel = row.is_scheduled
                ? ('Scheduled • ' + (row.schedule_slot || 'Slot not set'))
                : 'New Checkout';
            const dateSource = row.scheduled_for || row.created_at || '';
            const scheduleDate = dateSource ? dateSource.substring(0, 10) : '';
            const targetUrl = scheduleDate
                ? `{{ route('admin.schedule') }}?date=${encodeURIComponent(scheduleDate)}`
                : `{{ route('admin.schedule') }}`;

            return `
                <div class="border rounded-lg p-2">
                    <p class="font-medium break-all">#${row.order_number || ''}</p>
                    <p class="text-[11px] text-gray-500">${row.customer_name || 'Customer'}</p>
                    <p class="text-[11px] text-green-700">${typeLabel}</p>
                    <a href="${targetUrl}"
                       class="text-[11px] text-blue-600 hover:underline">View</a>
                </div>
            `;
        }).join('');
    } catch (_) {}
}

async function markAdminNotificationsRead() {
    const tokenNode = document.querySelector('meta[name="csrf-token"]');
    if (!tokenNode) return;

    try {
        await fetch("{{ route('admin.notifications.mark-read') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': tokenNode.getAttribute('content'),
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });
    } catch (_) {}
}

document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('adminNotificationWrapper')) return;

    fetchAdminNotifications();
    setInterval(fetchAdminNotifications, 10000);
});

// Close when clicking outside
document.addEventListener('click', function(event) {
    const wrapper = document.getElementById('notificationWrapper');
    const dropdown = document.getElementById('notifDropdown');
    const adminWrapper = document.getElementById('adminNotificationWrapper');
    const adminDropdown = document.getElementById('adminNotifDropdown');

    if (wrapper && !wrapper.contains(event.target)) {
        if (dropdown) dropdown.classList.add('hidden');
    }

    if (adminWrapper && !adminWrapper.contains(event.target)) {
        if (adminDropdown) adminDropdown.classList.add('hidden');
    }
});
</script>

<script>
// Mobile menu fallback (works even if Alpine/Vite isn't available)
document.addEventListener('DOMContentLoaded', function(){
    // If Alpine is present, let Alpine handle the menu toggling
    if (window.Alpine) return;

    const btn = document.getElementById('mobileToggle');
    const wrapper = document.getElementById('mobileMenuWrapper');
    const menu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileOverlay');
    if (!btn || !menu || !wrapper) return;

    [wrapper, menu, overlay].forEach(function(el) {
        if (!el) return;
        el.removeAttribute('x-cloak');
    });

    wrapper.classList.add('hidden');
    menu.classList.add('hidden', 'translate-x-full');
    menu.classList.remove('translate-x-0');
    if (overlay) overlay.classList.add('hidden');

    function openMenu() {
        wrapper.classList.remove('hidden');
        menu.classList.remove('hidden', 'translate-x-full');
        menu.classList.add('translate-x-0');
        if (overlay) overlay.classList.remove('hidden');
    }

    function closeMenu() {
        menu.classList.add('hidden', 'translate-x-full');
        menu.classList.remove('translate-x-0');
        if (overlay) overlay.classList.add('hidden');
        wrapper.classList.add('hidden');
    }

    btn.addEventListener('click', function(e){
        e.stopPropagation();
        if (menu.classList.contains('hidden')) openMenu(); else closeMenu();
    });

    // Close when clicking outside
    document.addEventListener('click', function(ev){
        if (!menu.contains(ev.target) && !btn.contains(ev.target)) {
            closeMenu();
        }
    });

    // Hide menu when a link inside it is clicked
    menu.querySelectorAll('a').forEach(function(a){
        a.addEventListener('click', function(){ closeMenu(); });
    });
});
</script>
