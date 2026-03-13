<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const mobileOpen = ref(false);
const scrolled = ref(false);
const adminOpen = ref(false);
const adminProfileOpen = ref(false);
const mobileAdminOpen = ref(false);
const customerNotifOpen = ref(false);
const adminNotifOpen = ref(false);
const toastMessage = ref('');
const toastVisible = ref(false);
let toastTimer = null;
let adminMenuCloseTimer = null;
let adminProfileCloseTimer = null;
let customerNotifCloseTimer = null;
let adminNotifCloseTimer = null;

const POLL_INTERVAL_MS = 4000;

const customerOrders = ref([]);
const customerNotifCount = ref(0);
const lastCustomerStatuses = ref({});
let customerPollTimer = null;

const adminNotifications = ref([]);
const adminNotifCount = ref(0);
let adminPollTimer = null;

const user = computed(() => page.props.auth?.user || null);
const isAdmin = computed(() => !!user.value?.is_admin);
const isAdminPage = computed(() => String(page.component || '').startsWith('Admin/'));
const homeHref = computed(() => (isAdmin.value ? '/admin/dashboard' : '/dashboard'));
const adminAvatarUrl = computed(() => user.value?.profile_photo_url || null);
const adminInitial = computed(() => String(user.value?.name || 'A').trim().charAt(0).toUpperCase() || 'A');
const showCustomerBell = computed(() => !isAdmin.value && customerOrders.value.length > 0);
const flashSuccess = computed(() => page.props.flash?.success || '');
const CUSTOMER_NOTIF_CACHE_KEY = 'app.customer.notifications';

function onWindowScroll() {
  scrolled.value = window.scrollY > 10;
}

function onDocumentClick(event) {
  const target = event.target;
  if (!(target instanceof Element)) return;
  if (!target.closest('[data-admin-dropdown]')) adminOpen.value = false;
  if (!target.closest('[data-admin-profile-dropdown]')) adminProfileOpen.value = false;
  if (!target.closest('[data-customer-notif]')) customerNotifOpen.value = false;
  if (!target.closest('[data-admin-notif]')) adminNotifOpen.value = false;
}

function closeMenus() {
  mobileOpen.value = false;
  adminOpen.value = false;
  adminProfileOpen.value = false;
  mobileAdminOpen.value = false;
  customerNotifOpen.value = false;
  adminNotifOpen.value = false;
}

function clearHoverTimer(timerName) {
  if (!timerName) return;
  window.clearTimeout(timerName);
}

function loadCustomerNotificationCache() {
  try {
    const raw = window.sessionStorage.getItem(CUSTOMER_NOTIF_CACHE_KEY);
    if (!raw) return;

    const cached = JSON.parse(raw);
    customerOrders.value = Array.isArray(cached.orders) ? cached.orders : [];
    customerNotifCount.value = Number(cached.count || 0);
    lastCustomerStatuses.value = cached.statuses && typeof cached.statuses === 'object' ? cached.statuses : {};
  } catch (_) {}
}

function saveCustomerNotificationCache() {
  try {
    window.sessionStorage.setItem(CUSTOMER_NOTIF_CACHE_KEY, JSON.stringify({
      orders: customerOrders.value,
      count: customerNotifCount.value,
      statuses: lastCustomerStatuses.value,
    }));
  } catch (_) {}
}

function closeToast() {
  toastVisible.value = false;
  if (toastTimer) {
    window.clearTimeout(toastTimer);
    toastTimer = null;
  }
}

function showToast(message) {
  if (!message) return;

  toastMessage.value = message;
  toastVisible.value = true;

  if (toastTimer) {
    window.clearTimeout(toastTimer);
  }

  toastTimer = window.setTimeout(() => {
    toastVisible.value = false;
    toastTimer = null;
  }, 5000);
}

function consumeStoredToast() {
  if (isAdminPage.value) return;

  const storedMessage = window.sessionStorage.getItem('app.toast.success');
  if (!storedMessage) return;

  window.sessionStorage.removeItem('app.toast.success');
  showToast(storedMessage);
}

function logout() {
  router.post('/logout');
}

function getCustomerStateKey(row) {
  return `${row.status || ''}:${row.approval_status || ''}`;
}

async function fetchCustomerStatuses() {
  if (isAdmin.value || document.hidden) return;

  try {
    const response = await window.axios.get('/check-order-status');
    const rows = Array.isArray(response.data) ? response.data : [];
    customerOrders.value = rows;

    const nextMap = {};
    let changed = 0;
    rows.forEach((row) => {
      const nextState = getCustomerStateKey(row);
      nextMap[row.id] = nextState;
      if (lastCustomerStatuses.value[row.id] && lastCustomerStatuses.value[row.id] !== nextState) {
        changed++;
      }
    });

    if (changed > 0) {
      customerNotifCount.value += changed;
    }

    lastCustomerStatuses.value = nextMap;
    saveCustomerNotificationCache();
  } catch (_) {}
}

async function fetchAdminNotifications() {
  if (!isAdmin.value || document.hidden) return;

  try {
    const response = await window.axios.get('/admin/notifications');
    adminNotifications.value = response.data?.notifications || [];
    adminNotifCount.value = Number(response.data?.unread_count || 0);
  } catch (_) {}
}

async function markAdminRead() {
  if (!isAdmin.value) return;
  try {
    await window.axios.post('/admin/notifications/mark-read');
    adminNotifCount.value = 0;
  } catch (_) {}
}

function toggleCustomerNotif() {
  customerNotifOpen.value = !customerNotifOpen.value;
  if (customerNotifOpen.value) {
    adminNotifOpen.value = false;
    adminOpen.value = false;
    customerNotifCount.value = 0;
  }
}

function openCustomerNotif() {
  clearHoverTimer(customerNotifCloseTimer);
  customerNotifCloseTimer = null;
  customerNotifOpen.value = true;
  adminNotifOpen.value = false;
  adminOpen.value = false;
  customerNotifCount.value = 0;
}

function closeCustomerNotifWithDelay() {
  clearHoverTimer(customerNotifCloseTimer);
  customerNotifCloseTimer = window.setTimeout(() => {
    customerNotifOpen.value = false;
    customerNotifCloseTimer = null;
  }, 180);
}

async function refreshNotificationState() {
  if (isAdmin.value) {
    await fetchAdminNotifications();
    return;
  }

  await fetchCustomerStatuses();
}

function handleLiveRefresh() {
  refreshNotificationState();
}

function handleStorage(event) {
  if (event.key !== 'app.live-refresh') return;
  refreshNotificationState();
}

function handleVisibilityChange() {
  if (!document.hidden) {
    refreshNotificationState();
  }
}

async function toggleAdminNotif() {
  adminNotifOpen.value = !adminNotifOpen.value;
  if (adminNotifOpen.value) {
    customerNotifOpen.value = false;
    adminOpen.value = false;
    await markAdminRead();
  }
}

function toggleAdminMenu() {
  adminOpen.value = !adminOpen.value;
  if (adminOpen.value) {
    customerNotifOpen.value = false;
    adminNotifOpen.value = false;
  }
}

async function openAdminNotif() {
  clearHoverTimer(adminNotifCloseTimer);
  adminNotifCloseTimer = null;
  adminNotifOpen.value = true;
  customerNotifOpen.value = false;
  adminOpen.value = false;
  await markAdminRead();
}

function closeAdminNotifWithDelay() {
  clearHoverTimer(adminNotifCloseTimer);
  adminNotifCloseTimer = window.setTimeout(() => {
    adminNotifOpen.value = false;
    adminNotifCloseTimer = null;
  }, 180);
}

function openAdminMenu() {
  clearHoverTimer(adminMenuCloseTimer);
  adminMenuCloseTimer = null;
  adminOpen.value = true;
  adminProfileOpen.value = false;
  customerNotifOpen.value = false;
  adminNotifOpen.value = false;
}

function closeAdminMenuWithDelay() {
  clearHoverTimer(adminMenuCloseTimer);
  adminMenuCloseTimer = window.setTimeout(() => {
    adminOpen.value = false;
    adminMenuCloseTimer = null;
  }, 180);
}

function openAdminProfileMenu() {
  clearHoverTimer(adminProfileCloseTimer);
  adminProfileCloseTimer = null;
  adminProfileOpen.value = true;
  adminOpen.value = false;
  customerNotifOpen.value = false;
  adminNotifOpen.value = false;
}

function closeAdminProfileMenuWithDelay() {
  clearHoverTimer(adminProfileCloseTimer);
  adminProfileCloseTimer = window.setTimeout(() => {
    adminProfileOpen.value = false;
    adminProfileCloseTimer = null;
  }, 180);
}

onMounted(async () => {
  onWindowScroll();
  loadCustomerNotificationCache();
  window.addEventListener('scroll', onWindowScroll);
  window.addEventListener('focus', handleLiveRefresh);
  window.addEventListener('storage', handleStorage);
  window.addEventListener('app:live-refresh', handleLiveRefresh);
  document.addEventListener('click', onDocumentClick);
  document.addEventListener('visibilitychange', handleVisibilityChange);

  await refreshNotificationState();
  customerPollTimer = window.setInterval(fetchCustomerStatuses, POLL_INTERVAL_MS);
  consumeStoredToast();

  if (isAdmin.value) {
    adminPollTimer = window.setInterval(fetchAdminNotifications, POLL_INTERVAL_MS);
  }
});

watch(
  flashSuccess,
  (message) => {
    if (!isAdminPage.value && typeof message === 'string' && message.trim()) {
      showToast(message.trim());
    }
  },
  { immediate: true }
);

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onWindowScroll);
  window.removeEventListener('focus', handleLiveRefresh);
  window.removeEventListener('storage', handleStorage);
  window.removeEventListener('app:live-refresh', handleLiveRefresh);
  document.removeEventListener('click', onDocumentClick);
  document.removeEventListener('visibilitychange', handleVisibilityChange);
  if (customerPollTimer) window.clearInterval(customerPollTimer);
  if (adminPollTimer) window.clearInterval(adminPollTimer);
  if (toastTimer) window.clearTimeout(toastTimer);
  if (adminMenuCloseTimer) window.clearTimeout(adminMenuCloseTimer);
  if (adminProfileCloseTimer) window.clearTimeout(adminProfileCloseTimer);
  if (customerNotifCloseTimer) window.clearTimeout(customerNotifCloseTimer);
  if (adminNotifCloseTimer) window.clearTimeout(adminNotifCloseTimer);
});
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-x-4 opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-4 opacity-0"
    >
      <div
        v-if="toastVisible"
        class="fixed right-4 top-4 z-[10050] w-full max-w-sm rounded-2xl border border-green-200 bg-white shadow-xl"
      >
        <div class="flex items-start gap-3 p-4">
          <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
            ✓
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-900">Success</p>
            <p class="mt-1 text-sm text-gray-600">{{ toastMessage }}</p>
          </div>
          <button
            type="button"
            @click="closeToast"
            class="text-lg leading-none text-gray-400 transition hover:text-gray-700"
            aria-label="Close notification"
          >
            ×
          </button>
        </div>
      </div>
    </transition>

    <nav
      class="sticky top-0 z-[9999] py-3 transition-[background-color,box-shadow,backdrop-filter] duration-300 ease-out"
      :class="scrolled ? 'bg-white/80 backdrop-blur-md shadow-lg' : 'bg-[#3a5a40]'"
    >
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 px-4 sm:px-6">
        <div class="flex items-center min-w-0">
          <Link :href="homeHref" class="flex items-center gap-3 min-w-0">
            <img
              :src="'/images/logo-blue.png'"
              class="h-10 w-10 shrink-0 object-contain"
              alt="Logo"
            >
            <span class="truncate text-base sm:text-lg font-bold tracking-wide" :class="scrolled ? 'text-green-800' : 'text-white'">
              Party Tray
            </span>
          </Link>
        </div>

        <div class="flex shrink-0 items-center gap-4 sm:gap-6">
          <div class="hidden md:flex items-center space-x-8 font-medium">
            <Link :href="homeHref" class="transition hover:text-green-600" :class="scrolled ? 'text-gray-700' : 'text-white'">Home</Link>
            <Link href="/menu" class="transition hover:text-green-600" :class="scrolled ? 'text-gray-700' : 'text-white'">Menu</Link>
            <Link href="/cart" class="transition hover:text-green-600" :class="scrolled ? 'text-gray-700' : 'text-white'">Cart</Link>
            <Link href="/orders" class="transition hover:text-green-600" :class="scrolled ? 'text-gray-700' : 'text-white'">Orders</Link>

            <div
              v-if="isAdmin"
              class="relative"
              data-admin-dropdown
              @mouseenter="openAdminMenu"
              @mouseleave="closeAdminMenuWithDelay"
            >
              <button
                type="button"
                @mouseenter="openAdminMenu"
                class="flex items-center gap-1 transition hover:text-green-600"
                :class="scrolled ? 'text-gray-700' : 'text-white'"
              >
                Admin ▾
              </button>

              <div v-if="adminOpen" class="absolute right-0 top-full z-50 w-52 pt-2">
                <div class="rounded-xl border bg-white py-2 shadow-xl">
                <Link href="/admin/dashboard" class="block px-4 py-2 text-sm hover:bg-gray-100">Dashboard</Link>
                <Link href="/admin/categories" class="block px-4 py-2 text-sm hover:bg-gray-100">Categories</Link>
                <Link href="/admin/orders" class="block px-4 py-2 text-sm hover:bg-gray-100">Orders</Link>
                <Link href="/admin/foods" class="block px-4 py-2 text-sm hover:bg-gray-100">Foods</Link>
                <Link href="/admin/schedule" class="block px-4 py-2 text-sm hover:bg-gray-100">Schedule</Link>
                </div>
              </div>
            </div>
          </div>

          <div class="flex shrink-0 items-center gap-1.5 sm:gap-3">
          <div
            class="relative flex h-9 w-9 items-center justify-center sm:h-10 sm:w-10"
            data-customer-notif
            @mouseenter="openCustomerNotif"
            @mouseleave="closeCustomerNotifWithDelay"
          >
            <button
              v-show="showCustomerBell"
              @mouseenter="openCustomerNotif"
              class="relative inline-flex h-9 w-9 items-center justify-center rounded-full border text-base transition sm:h-10 sm:w-10 sm:text-lg"
              :class="scrolled ? 'text-gray-700 border-gray-300 hover:bg-gray-100' : 'text-white border-white/20 hover:bg-white/10'"
            >
              🔔
              <span v-if="customerNotifCount > 0" class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] min-w-[16px] h-4 px-1 rounded-full inline-flex items-center justify-center">
                {{ customerNotifCount > 99 ? '99+' : customerNotifCount }}
              </span>
            </button>

            <div v-if="showCustomerBell && customerNotifOpen" class="absolute right-0 top-full z-50 w-72 max-w-[calc(100vw-1rem)] pt-2">
              <div class="max-h-[70vh] overflow-y-auto rounded-xl border bg-white p-3 shadow-xl sm:p-4">
              <h3 class="font-semibold text-sm mb-2">My Orders</h3>
              <div v-if="!customerOrders.length" class="text-xs text-gray-500">No orders in session yet.</div>
              <div v-for="order in customerOrders" :key="order.id" class="border-b py-2 last:border-0">
                <p class="text-xs font-medium">#{{ order.order_number }}</p>
                <p class="text-xs text-gray-600">{{ order.status_label || order.status }}</p>
                <Link :href="`/orders/${order.id}`" class="text-xs text-green-600 hover:underline">View Details →</Link>
              </div>
              <div class="mt-3 text-center">
                <Link href="/orders" class="text-xs sm:text-sm text-blue-600 hover:underline">View All Orders</Link>
              </div>
              </div>
            </div>
          </div>

          <div
            v-if="isAdmin"
            class="relative flex items-center"
            data-admin-notif
            @mouseenter="openAdminNotif"
            @mouseleave="closeAdminNotifWithDelay"
          >
            <button
              @mouseenter="openAdminNotif"
              class="relative inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full border text-base sm:text-lg transition"
              :class="scrolled ? 'text-gray-700 border-gray-300 hover:bg-gray-100' : 'text-white border-white/20 hover:bg-white/10'"
            >
              🔔
              <span v-if="adminNotifCount > 0" class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] min-w-[16px] h-4 px-1 rounded-full inline-flex items-center justify-center">
                {{ adminNotifCount > 99 ? '99+' : adminNotifCount }}
              </span>
            </button>

            <div v-if="adminNotifOpen" class="absolute right-0 top-full z-50 w-72 max-w-[calc(100vw-1rem)] pt-2">
              <div class="max-h-[26rem] overflow-y-auto rounded-xl border bg-white p-3 shadow-xl sm:p-4">
              <h3 class="font-semibold text-sm mb-2">Admin Notifications</h3>
              <div v-if="!adminNotifications.length" class="text-xs text-gray-500">No notifications found.</div>
              <div v-for="item in adminNotifications" :key="item.id" class="border-b py-2 last:border-0">
                <p class="text-xs font-medium">
                  {{ item.type === 'low_stock' ? item.food_name : `#${item.order_number}` }}
                </p>
                <p v-if="item.type === 'low_stock'" class="text-xs text-gray-600">
                  Only {{ item.available_quantity }} left
                </p>
                <p v-else class="text-xs text-gray-600">{{ item.customer_name }}</p>
                <p
                  class="text-xs"
                  :class="item.type === 'cancelled' ? 'text-red-600' : item.type === 'low_stock' ? 'text-amber-600' : 'text-green-700'"
                >
                  {{
                    item.type === 'low_stock'
                      ? 'Low Stock Warning'
                      : item.type === 'cancelled'
                      ? 'Order Cancelled'
                      : item.is_scheduled
                        ? `Scheduled • ${item.schedule_slot || 'N/A'}`
                        : 'New Checkout'
                  }}
                </p>
                <Link
                  :href="item.type === 'low_stock' ? '/admin/foods' : `/admin/schedule?date=${(item.scheduled_for || item.created_at || '').substring(0,10)}`"
                  class="text-xs text-blue-600 hover:underline"
                >
                  View
                </Link>
              </div>
              <div class="mt-3 text-center">
                <Link href="/admin/orders" class="text-xs sm:text-sm text-blue-600 hover:underline">View All Orders</Link>
              </div>
              </div>
            </div>
          </div>

          <button
            @click="mobileOpen = !mobileOpen"
            class="md:hidden inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-full border text-xl sm:text-2xl"
            :class="scrolled ? 'text-gray-800 border-gray-300' : 'text-white border-white/20'"
            aria-label="Open menu"
          >
            ☰
          </button>

          <div
            v-if="isAdmin"
            class="relative"
            data-admin-profile-dropdown
            @mouseenter="openAdminProfileMenu"
            @mouseleave="closeAdminProfileMenuWithDelay"
          >
            <button
              type="button"
              @mouseenter="openAdminProfileMenu"
              class="inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-full border transition sm:h-10 sm:w-10"
              :class="scrolled ? 'border-gray-300 hover:bg-gray-100' : 'border-white/20 hover:bg-white/10'"
              aria-label="Admin profile"
              title="Profile"
            >
              <img
                v-if="adminAvatarUrl"
                :src="adminAvatarUrl"
                alt="Admin profile"
                class="h-full w-full object-cover"
              >
              <span
                v-else
                class="flex h-full w-full items-center justify-center bg-green-600 text-sm font-semibold text-white"
              >
                {{ adminInitial }}
              </span>
            </button>

            <div v-if="adminProfileOpen" class="absolute -right-1 top-full z-50 w-44 pt-1">
              <div class="overflow-hidden rounded-2xl border bg-white py-2 shadow-xl ring-1 ring-black/5">
                <Link href="/profile" class="block px-4 py-2 text-sm hover:bg-gray-100">Profile</Link>
                <button type="button" @click="logout" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-100">Logout</button>
              </div>
            </div>
          </div>
          </div>
        </div>
      </div>

      <div v-if="mobileOpen" class="md:hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="mobileOpen = false"></div>

        <aside class="absolute right-0 top-0 h-full w-[85%] max-w-xs bg-white shadow-2xl p-4 space-y-4 overflow-y-auto">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <img
                :src="'/images/logo-blue.png'"
                class="h-9 w-9 object-contain"
                alt="Logo"
              >
              <span class="font-bold text-lg">Party Tray</span>
            </div>
            <button @click="mobileOpen = false" class="text-xl p-2 rounded hover:bg-gray-100">✕</button>
          </div>

          <nav class="mt-2 flex flex-col gap-2 text-sm">
            <Link :href="homeHref" @click="closeMenus" class="block rounded-lg px-3 py-3 text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">Home</Link>
            <Link href="/menu" @click="closeMenus" class="block rounded-lg px-3 py-3 text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">Menu</Link>
            <Link href="/cart" @click="closeMenus" class="block rounded-lg px-3 py-3 text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">Cart</Link>
            <Link href="/orders" @click="closeMenus" class="block rounded-lg px-3 py-3 text-gray-700 font-medium hover:bg-green-50 hover:text-green-600">Orders</Link>
          </nav>

          <div v-if="isAdmin" class="border-t pt-4">
            <button
              type="button"
              @click="mobileAdminOpen = !mobileAdminOpen"
              class="flex w-full items-center justify-between rounded-lg px-3 py-3 text-left text-gray-700 font-medium hover:bg-green-50 hover:text-green-600"
            >
              <span>Admin</span>
              <span>{{ mobileAdminOpen ? '−' : '+' }}</span>
            </button>
            <div v-if="mobileAdminOpen" class="mt-2 space-y-2">
              <Link href="/profile" @click="closeMenus" class="block px-3 py-2 rounded hover:bg-gray-50 text-sm text-gray-600">Profile</Link>
              <Link href="/admin/orders" @click="closeMenus" class="block px-3 py-2 rounded hover:bg-gray-50 text-sm text-gray-600">View Orders</Link>
              <Link href="/admin/dashboard" @click="closeMenus" class="block px-3 py-2 rounded hover:bg-gray-50 text-sm text-gray-600">Settings</Link>
            </div>
          </div>
        </aside>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
      <slot />
    </main>
  </div>
</template>
