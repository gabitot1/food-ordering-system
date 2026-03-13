<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import AppLayout from '../../../Layouts/AppLayout.vue';

const POLL_INTERVAL_MS = 4000;
let pollTimer = null;
const isEditing = ref(false);

const props = defineProps({
  orders: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    required: true,
  },
  tabs: {
    type: Array,
    default: () => [],
  },
  status_options: {
    type: Array,
    default: () => [],
  },
  approval_status_options: {
    type: Array,
    default: () => [],
  },
  payment_status_options: {
    type: Array,
    default: () => [],
  },
  flash: {
    type: Object,
    default: () => ({ success: null, error: null }),
  },
});

const filtersForm = reactive({
  q: props.filters.q || '',
  approval_status: props.filters.approval_status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
});

function formatDateValue(value) {
  if (!(value instanceof Date) || Number.isNaN(value.getTime())) {
    return '';
  }

  const year = value.getFullYear();
  const month = String(value.getMonth() + 1).padStart(2, '0');
  const day = String(value.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function parseDateValue(value) {
  if (!value) return null;

  const parsed = new Date(`${value}T00:00:00`);
  return Number.isNaN(parsed.getTime()) ? null : parsed;
}

const dateFromPicker = computed({
  get() {
    return parseDateValue(filtersForm.date_from);
  },
  set(value) {
    filtersForm.date_from = formatDateValue(value);
  },
});

const dateToPicker = computed({
  get() {
    return parseDateValue(filtersForm.date_to);
  },
  set(value) {
    filtersForm.date_to = formatDateValue(value);
  },
});

function applyFilters() {
  router.get(
    '/admin/orders',
    {
      status: props.filters.status || undefined,
      approval_status: filtersForm.approval_status || undefined,
      q: filtersForm.q || undefined,
      date_from: filtersForm.date_from || undefined,
      date_to: filtersForm.date_to || undefined,
    },
    { preserveState: true, replace: true }
  );
}

function selectTab(statusKey) {
  router.get(
    '/admin/orders',
    {
      status: statusKey || undefined,
      approval_status: filtersForm.approval_status || undefined,
      q: filtersForm.q || undefined,
      date_from: filtersForm.date_from || undefined,
      date_to: filtersForm.date_to || undefined,
    },
    { preserveState: true, replace: true }
  );
}

function updateOrder(order) {
  router.patch(
    `/admin/orders/${order.id}`,
    {
      status: order.status,
      approval_status: order.approval_status,
      payment_status: order.payment_status,
      approval_note: order.approval_note,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        isEditing.value = false;
      },
    }
  );
}

function markEditing() {
  isEditing.value = true;
}

function handleApprovalChange(order) {
  markEditing();

  if (order.approval_status === 'disapproved') {
    order.status = 'cancelled';
    return;
  }

  if (order.approval_status === 'pending') {
    order.status = 'pending';
    return;
  }

  if (order.status === 'cancelled') {
    order.status = 'pending';
  }
}

function exportPdf() {
  const params = new URLSearchParams();
  if (props.filters.status) params.set('status', props.filters.status);
  if (filtersForm.approval_status) params.set('approval_status', filtersForm.approval_status);
  if (filtersForm.q) params.set('q', filtersForm.q);
  if (filtersForm.date_from) params.set('date_from', filtersForm.date_from);
  if (filtersForm.date_to) params.set('date_to', filtersForm.date_to);
  const query = params.toString();
  window.location.href = `/admin/orders/export/pdf${query ? `?${query}` : ''}`;
}

function reloadOrders() {
  if (isEditing.value) return;

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
  <Head title="Admin Orders" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-2xl font-bold text-green-600 tracking-tight">Admin Orders</h1>
    </section>

    <!-- <section v-if="flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
      {{ flash.success }}
    </section> -->
    <section v-if="flash?.error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
      {{ flash.error }}
    </section>

    <form
      @submit.prevent="applyFilters"
      class="sticky top-20 z-20 mb-6 rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-sm backdrop-blur"
    >
      <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
        <div class="min-w-0 flex-1">
          <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Search</label>
          <input
            v-model="filtersForm.q"
            type="text"
            class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100"
            placeholder="Order number, customer name, or contact"
          >
        </div>

        <div class="w-28 shrink-0">
          <label class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Approval</label>
          <select v-model="filtersForm.approval_status" class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-2 py-2.5 text-xs text-gray-700 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-100">
            <option value="">All approvals</option>
            <option v-for="opt in approval_status_options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <VueDatePicker
            v-model="dateFromPicker"
            auto-apply
            :enable-time-picker="false"
            format="yyyy-MM-dd"
            model-type="yyyy-MM-dd"
            placeholder="From"
            input-class-name="dp-orders-filter-input"
            menu-class-name=  "dp-orders-filter-menu"
          />
          <span class="text-xs font-medium text-gray-400">to</span>
          <VueDatePicker
            v-model="dateToPicker"
            auto-apply
            :enable-time-picker="false"
            format="yyyy-MM-dd"
            model-type="yyyy-MM-dd"
            placeholder="To"
            input-class-name="dp-orders-filter-input"
            menu-class-name="dp-orders-filter-menu"
          />
        </div>

        <div class="flex shrink-0 gap-2">
          <button type="submit" class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-green-700">Apply</button>
          <button type="button" @click="exportPdf" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Export</button>
        </div>
      </div>
    </form>

    <div class="flex gap-3 flex-wrap items-center justify-center mb-6">
      <button
        v-for="tab in tabs"
        :key="tab.key || 'all'"
        type="button"
        @click="selectTab(tab.key)"
        class="px-4 py-2 text-xs rounded-full transition-all duration-300"
        :class="(filters.status || '') === (tab.key || '') ? 'bg-green-600 text-white shadow' : 'bg-white border border-gray-200 text-gray-600 hover:bg-green-50'"
      >
        {{ tab.label }}
      </button>
    </div>

    <section v-if="!orders?.data?.length" class="bg-white rounded-2xl shadow border p-12 text-center">
      <p class="text-gray-500 text-lg">No orders found.</p>
      <p class="text-sm text-gray-400 mt-2">Try changing filters or search criteria.</p>
    </section>

    <section v-else class="bg-white rounded-2xl shadow border overflow-hidden divide-y">
      <article v-for="order in orders.data" :key="order.id" class="p-6 hover:bg-green-50/60 transition-all duration-300">
        <div class="flex flex-col md:flex-row md:justify-between gap-6">
          <div class="space-y-2">
            <div class="text-sm font-semibold text-green-600">{{ order.order_number }}</div>
            <div class="text-sm text-gray-600">{{ order.customer_name }}</div>
            <div class="text-sm text-gray-500">
              ₱{{ Number(order.total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
              • {{ order.items_count }} items
            </div>
            <div class="text-xs text-gray-500">
              Purchased: {{ order.purchase_time_label }}
            </div>
            <div class="text-xs text-gray-500">
              {{ order.schedule_type_label }}: {{ order.scheduled_time_label }}
            </div>
          </div>

          <div class="flex items-center gap-3 flex-wrap">
            <span class="px-3 py-1 text-xs rounded-full" :class="order.status_color">{{ order.status_label }}</span>
            <span class="px-3 py-1 text-xs rounded-full" :class="order.approval_color">{{ order.approval_label }}</span>
            <span class="px-3 py-1 text-xs rounded-full" :class="order.payment_color">{{ order.payment_label }}</span>
            <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700">{{ order.payment_method }}</span>
          </div>
        </div>

        <form @submit.prevent="updateOrder(order)" class="mt-6">
          <div class="grid md:grid-cols-4 gap-4">
            <select
              v-model="order.approval_status"
              class="border border-gray-300 rounded-xl px-4 py-2 text-sm"
              @focus="markEditing"
              @change="handleApprovalChange(order)"
            >
              <option v-for="opt in approval_status_options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>

            <select
              v-model="order.status"
              class="border border-gray-300 rounded-xl px-4 py-2 text-sm"
              :disabled="order.approval_status !== 'approved'"
              @focus="markEditing"
              @change="markEditing"
            >
              <option v-for="opt in status_options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>

            <select
              v-model="order.payment_status"
              class="border border-gray-300 rounded-xl px-4 py-2 text-sm"
              @focus="markEditing"
              @change="markEditing"
            >
              <option v-for="opt in payment_status_options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700">
              Update
            </button>
          </div>

          <textarea
            v-if="order.approval_status === 'disapproved'"
            v-model="order.approval_note"
            rows="2"
            class="mt-4 w-full border border-gray-300 rounded-xl px-4 py-3 text-sm"
            placeholder="Reason for disapproval"
            @focus="markEditing"
            @input="markEditing"
          />
        </form>
      </article>
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

<style scoped>
:deep(.dp-orders-filter-input) {
  width: 150px;
  border-radius: 0.75rem;
  border: 1px solid rgb(229 231 235);
  background: rgb(255 255 255);
  padding: 0.625rem 0.75rem;
  font-size: 0.875rem;
  color: rgb(55 65 81);
  outline: none;
  transition: border-color 150ms ease, box-shadow 150ms ease;
}

:deep(.dp-orders-filter-input:focus) {
  border-color: rgb(34 197 94);
  box-shadow: 0 0 0 2px rgb(220 252 231);
}

:deep(.dp-orders-filter-menu) {
  z-index: 60;
}
</style>
