<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const POLL_INTERVAL_MS = 4000;
let pollTimer = null;

const props = defineProps({
  orders: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '' }),
  },
  // flash: {
  //   type: Object,
  //   default: () => ({ success: null }),
  // },
});

const search = ref(props.filters.search || '');
watch(
  () => props.filters.search,
  (value) => {
    search.value = value || '';
  }
);

function submitSearch() {
  router.get(
    '/orders',
    { search: search.value || undefined },
    { preserveState: true, replace: true }
  );
}

function resetSearch() {
  search.value = '';
  router.get('/orders', {}, { preserveState: true, replace: true });
}

const hasOrders = computed(() => Array.isArray(props.orders?.data) && props.orders.data.length > 0);

function progressBarStyle(order) {
  const backgroundColor = order.display_status === 'cancelled'
    ? '#ef4444'
    : order.display_status === 'awaiting_approval'
      ? '#f97316'
      : '#22c55e';

  return {
    width: order.progress_width,
    backgroundColor,
  };
}

function reloadOrders() {
  router.reload({
    only: ['orders', 'flash'],
    preserveScroll: true,
    preserveState: true,
  });
}

function handleLiveRefresh() {
  reloadOrders();
}

onMounted(() => {
  window.addEventListener('app:live-refresh', handleLiveRefresh);
  window.addEventListener('focus', handleLiveRefresh);
  pollTimer = window.setInterval(() => {
    if (!document.hidden) {
      reloadOrders();
    }
  }, POLL_INTERVAL_MS);
});

onBeforeUnmount(() => {
  window.removeEventListener('app:live-refresh', handleLiveRefresh);
  window.removeEventListener('focus', handleLiveRefresh);
  if (pollTimer) window.clearInterval(pollTimer);
});
</script>

<template>
  <Head title="My Orders" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-lg sm:text-2xl font-semibold text-green-800 tracking-wide">My Orders</h1>
    </section>

    <section v-if="flash?.success" class="mb-6 p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
      <p class="text-sm text-gray-700">{{ flash.success }}</p>
    </section>

    <section class="mb-5 sm:mb-6 bg-white p-3 sm:p-5 rounded-2xl shadow-sm border border-gray-200">
      <form @submit.prevent="submitSearch" class="flex flex-col sm:flex-row gap-3">
        <input
          v-model="search"
          type="text"
          name="search"
          placeholder="Search order number, name, or status..."
          class="flex-1 border border-gray-300 rounded-xl px-3 sm:px-4 py-2 text-xs sm:text-sm focus:ring-2 focus:ring-gray-900 focus:outline-none"
        />

        <button
          type="submit"
          class="w-full sm:w-auto px-4 sm:px-5 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-800 transition"
        >
          Search
        </button>

        <button
          v-if="filters?.search"
          type="button"
          @click="resetSearch"
          class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-xl text-xs sm:text-sm hover:bg-gray-100 transition text-center"
        >
          Reset
        </button>
      </form>
    </section>

    <section class="space-y-6">
      <article
        v-for="order in orders.data"
        :key="order.id"
        class="bg-white rounded-2xl shadow-sm border border-gray-200 p-3 sm:p-6 hover:shadow-md transition duration-300"
      >
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
          <div>
            <h3 class="text-sm sm:text-lg font-semibold text-gray-900 break-all sm:break-normal">
              Order #{{ order.order_number }}
            </h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">{{ order.created_at_label }}</p>
            <p class="mt-2 sm:mt-3 text-xs sm:text-sm text-gray-600">{{ order.items_count }} items</p>
          </div>

          <div class="text-left sm:text-right">
            <p class="text-xs sm:text-sm text-gray-500">Total</p>
            <p class="text-lg sm:text-xl font-semibold text-gray-900">
              ₱{{ Number(order.total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </p>
          </div>
        </div>

        <div class="mt-6">
          <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
            <div class="h-full transition-all duration-700" :style="progressBarStyle(order)"></div>
          </div>
        </div>

        <div class="mt-5 flex flex-col items-stretch sm:flex-row sm:justify-between sm:items-center gap-3">
          <div class="flex flex-wrap gap-2 sm:gap-3">
            <span class="px-3 py-1 text-[11px] sm:text-xs font-medium rounded-full" :class="order.status_color">
              {{ order.status_label }}
            </span>

            <!-- <span
              v-if="order.approval_status !== 'approved'"
              class="px-3 py-1 text-[11px] sm:text-xs font-medium rounded-full"
              :class="order.approval_status === 'disapproved' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'"
            >
              {{ order.approval_status === 'disapproved' ? 'Admin disapproved' : 'Waiting for admin approval' }}
            </span> -->

            <span class="px-3 py-1 text-[11px] sm:text-xs font-medium rounded-full" :class="order.payment_color">
              {{ order.payment_status }}
            </span>

            <span class="px-3 py-1 text-[11px] sm:text-xs font-medium rounded-full bg-gray-100 text-gray-700">
              {{ order.payment_method }}
            </span>
          </div>

          <Link
            :href="`/orders/${order.id}`"
            class="w-full sm:w-auto text-center px-4 py-2 text-xs sm:text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition"
          >
            View Details
          </Link>
        </div>

        <p v-if="order.approval_note" class="mt-3 text-xs text-red-600">
          Reason: {{ order.approval_note }}
        </p>
      </article>

      <div v-if="!hasOrders" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <p class="text-gray-500 text-sm">You haven’t placed any orders yet.</p>
      </div>
    </section>

    <section v-if="orders?.links?.length" class="mt-10 bg-white p-3 sm:p-4 rounded-2xl overflow-x-auto">
      <div class="flex flex-wrap items-center gap-2">
        <template v-for="(link, idx) in orders.links" :key="idx">
          <span
            v-if="!link.url"
            class="px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-200 text-gray-400"
            v-html="link.label"
          />
          <Link
            v-else
            :href="link.url"
            class="px-3 py-2 text-xs sm:text-sm rounded-lg border transition"
            :class="link.active ? 'bg-green-600 border-green-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
            v-html="link.label"
          />
        </template>
      </div>
    </section>
  </AppLayout>
</template>
