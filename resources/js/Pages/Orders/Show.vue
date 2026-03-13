<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const POLL_INTERVAL_MS = 4000;
let pollTimer = null;

const props = defineProps({
  order: {
    type: Object,
    required: true,
  },
  flash: {
    type: Object,
    default: () => ({ success: null, error: null }),
  },
});

const receiptModalOpen = ref(false);
const receiptLoading = ref(false);
const receiptError = ref('');
const receipt = ref(null);
const isWaitingApproval = computed(() => props.order.approval_status === 'pending');
const isCancelled = computed(() => props.order.status === 'cancelled');
const waitingApprovalColor = '#f97316';
const waitingApprovalTextColor = '#c2410c';
const cancelledColor = '#dc2626';
const cancelledTextColor = '#b91c1c';
const progressSteps = computed(() =>
  (props.order.steps || []).map((step, index) => (
    isWaitingApproval.value && index === 0
      ? { ...step, label: 'Waiting Approval' }
      : step
  ))
);
const progressWidth = computed(() => {
  const steps = progressSteps.value;
  const currentStep = Number(props.order.current_step || 1);

  if (!steps.length) return '0%';

  return `${((Math.max(currentStep, 1) - 0.5) * 100) / steps.length}%`;
});
const progressLineStyle = computed(() => ({
  width: progressWidth.value,
  backgroundColor: isCancelled.value
    ? cancelledColor
    : isWaitingApproval.value
      ? waitingApprovalColor
      : '#16a34a',
}));
const progressActiveStepStyle = computed(() => (
  isCancelled.value
    ? { backgroundColor: cancelledColor, color: '#ffffff' }
    : isWaitingApproval.value
      ? { backgroundColor: waitingApprovalColor, color: '#ffffff' }
      : { backgroundColor: '#16a34a', color: '#ffffff' }
));
const progressActiveTextStyle = computed(() => (
  isCancelled.value
    ? { color: cancelledTextColor }
    : isWaitingApproval.value
      ? { color: waitingApprovalTextColor }
      : { color: '#15803d' }
));

async function cancelOrder() {
  if (window.Swal) {
    const result = await window.Swal.fire({
      icon: 'warning',
      title: 'Cancel this order?',
      text: 'Only pending orders can be cancelled.',
      showCancelButton: true,
      confirmButtonText: 'Yes, cancel order',
      cancelButtonText: 'Keep order',
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
    });

    if (!result.isConfirmed) return;
  } else if (!confirm('Cancel this order? Only pending orders can be cancelled.')) {
    return;
  }

  router.post(`/orders/${props.order.id}/cancel`);
}

function formatMoney(value) {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

async function openReceiptModal() {
  receiptModalOpen.value = true;
  receiptLoading.value = true;
  receiptError.value = '';
  receipt.value = null;

  document.body.classList.add('overflow-hidden');

  try {
    const response = await window.axios.get(`/orders/${props.order.id}/receipt-modal`);
    receipt.value = response.data?.order || null;
    if (!receipt.value) {
      receiptError.value = 'Failed to load receipt.';
    }
  } catch (_) {
    receiptError.value = 'Failed to load receipt.';
  } finally {
    receiptLoading.value = false;
  }
}

function closeReceiptModal() {
  receiptModalOpen.value = false;
  document.body.classList.remove('overflow-hidden');
}

function handleKeyDown(event) {
  if (event.key === 'Escape' && receiptModalOpen.value) {
    closeReceiptModal();
  }
}

function printReceipt() {
  if (!receipt.value) return;

  const itemsHtml = (receipt.value.items || [])
    .map((item) => `
      <div style="margin-bottom:8px;">
        <div style="display:flex;justify-content:space-between;">
          <span>${item.food_name || 'Deleted'}</span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span>${item.quantity} x PHP ${formatMoney(item.price)}</span>
          <span>PHP ${formatMoney(item.subtotal)}</span>
        </div>
      </div>
    `)
    .join('');

  const html = `
    <html>
      <head>
        <title>Receipt</title>
        <style>
          body { font-family: monospace; padding: 16px; color: #111; }
          .receipt { width: 320px; margin: 0 auto; }
          .center { text-align: center; }
          .line { margin: 10px 0; }
          .total { display:flex; justify-content:space-between; font-weight:700; }
        </style>
      </head>
      <body>
        <div class="receipt">
          <div class="center">
            <h2>PARTY TRAY</h2>
            <p>F&b, 66 United Street, Mandaluyong</p>
            <p>--------------------------------</p>
          </div>
          <p>Order #: ${receipt.value.order_number || ''}</p>
          <p>Date: ${receipt.value.created_at || ''}</p>
          <p>Name: ${receipt.value.customer_name || ''}</p>
          <p>--------------------------------</p>
          ${itemsHtml}
          <p>--------------------------------</p>
          <div class="total"><span>TOTAL</span><span>PHP ${formatMoney(receipt.value.total)}</span></div>
          <p class="line">--------------------------------</p>
          <p class="center">Status: ${String(receipt.value.status || '').replaceAll('_', ' ')}</p>
          <div class="center" style="margin-top:16px;font-size:12px;">
            <p>Thank you for your order!</p>
            <p>Please come again</p>
          </div>
        </div>
      </body>
    </html>
  `;

  const printWindow = window.open('', '_blank', 'width=420,height=640');
  if (!printWindow) return;
  printWindow.document.write(html);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
}

function reloadOrder() {
  router.reload({
    only: ['order', 'flash'],
    preserveScroll: true,
    preserveState: true,
  });
}

function handleLiveRefresh() {
  reloadOrder();
}

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
  window.addEventListener('app:live-refresh', handleLiveRefresh);
  window.addEventListener('focus', handleLiveRefresh);
  pollTimer = window.setInterval(() => {
    if (!document.hidden) {
      reloadOrder();
    }
  }, POLL_INTERVAL_MS);
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeyDown);
  window.removeEventListener('app:live-refresh', handleLiveRefresh);
  window.removeEventListener('focus', handleLiveRefresh);
  document.body.classList.remove('overflow-hidden');
  if (pollTimer) window.clearInterval(pollTimer);
});
</script>

<template>
  <Head title="Order Details" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-2xl font-semibold text-green-600 tracking-tight">Order Details</h1>
    </section>

    <section v-if="flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
      {{ flash.success }}
    </section>
    <section v-if="flash?.error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
      {{ flash.error }}
    </section>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 md:p-8">
      <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between mb-8">
        <div>
          <h3 class="text-xl font-bold text-gray-900">Order No. {{ order.order_number }}</h3>
          <p class="text-sm text-gray-500 mt-1">{{ order.created_at_label }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 md:justify-end">
          <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="order.status_class">
            {{ order.status_label }}
          </span>
          <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="order.approval_class">
            {{ order.approval_status_label }}
          </span>
          <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="order.payment_class">
            Payment: {{ order.payment_status_label }}
          </span>
        </div>
      </div>

      <div
        v-if="order.approval_status !== 'approved'"
        class="mb-8 rounded-2xl border px-4 py-4 text-sm"
        :class="order.approval_status === 'disapproved' ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-200 bg-amber-50 text-amber-800'"
      >
        <p class="font-semibold">
          {{ order.approval_status === 'disapproved' ? 'This order was disapproved by the admin.' : 'This order is waiting for admin approval.' }}
        </p>
        <p v-if="order.approval_note" class="mt-1">Reason: {{ order.approval_note }}</p>
      </div>

      <div class="mb-10">
        <div class="relative">
          <div class="absolute top-5 left-0 w-full h-1 bg-gray-200 rounded"></div>
          <div class="absolute top-5 left-0 h-1 rounded transition-all duration-500" :style="progressLineStyle"></div>

          <div class="grid grid-cols-4 gap-6 relative">
            <div v-for="step in progressSteps" :key="step.key" class="text-center">
              <div
                class="mx-auto w-10 h-10 flex items-center justify-center rounded-full text-sm font-semibold"
                :class="order.current_step >= step.value ? '' : 'bg-gray-200 text-gray-600'"
                :style="order.current_step >= step.value ? progressActiveStepStyle : null"
              >
                <span v-if="isWaitingApproval && step.value === 1 && order.current_step >= step.value">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 6v6l4 2"/>
                    <circle cx="12" cy="12" r="9"/>
                  </svg>
                </span>
                <span v-else-if="order.current_step >= step.value">✓</span>
                <span v-else>{{ step.value }}</span>
              </div>
              <p class="mt-2 text-xs md:text-sm font-medium" :class="order.current_step >= step.value ? '' : 'text-gray-500'" :style="order.current_step >= step.value ? progressActiveTextStyle : null">
                {{ step.label }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div>
          <p class="text-xs uppercase tracking-wide text-gray-500">Customer Name</p>
          <p class="mt-1 font-semibold text-gray-800">{{ order.customer_name }}</p>
        </div>
        <div>
          <p class="text-xs uppercase tracking-wide text-gray-500">Contact</p>
          <p class="mt-1 font-semibold text-gray-800">{{ order.contact_number }}</p>
        </div>
        <div>
          <p class="text-xs uppercase tracking-wide text-gray-500">Address</p>
          <p class="mt-1 font-semibold text-gray-800">{{ order.address }}</p>
        </div>
        <div>
          <p class="text-xs uppercase tracking-wide text-gray-500">Delivery Option</p>
          <p class="mt-1 font-semibold text-gray-800 capitalize">{{ order.delivery_option }}</p>
        </div>
      </div>

      <div class="border-t border-gray-200 pt-6">
        <h4 class="font-semibold text-lg text-gray-900 mb-4">Order Items</h4>
        <div class="divide-y divide-gray-200">
          <div v-for="item in order.items" :key="item.id" class="py-4 flex items-start justify-between gap-4">
            <div>
              <p class="font-medium text-gray-800">{{ item.name }}</p>
              <p class="text-sm text-gray-500 mt-1">
                ₱{{ Number(item.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                x {{ item.quantity }}
              </p>
              <p v-if="item.instruction" class="text-xs text-gray-400 italic mt-2">Note: {{ item.instruction }}</p>
            </div>
            <div class="font-semibold text-gray-900 whitespace-nowrap">
              ₱{{ Number(item.subtotal).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </div>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between text-lg font-bold">
          <span>Total</span>
          <span class="text-green-700">
            ₱{{ Number(order.total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
          </span>
        </div>
      </div>

      <div class="mt-8 flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openReceiptModal"
          class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition"
        >
          View Receipt
        </button>

        <!-- <a
          :href="`/orders/${order.id}/receipt/download`"
          class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition"
        >
          Download Receipt
        </a> -->

        <button
          v-if="order.can_cancel"
          @click="cancelOrder"
          class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition"
        >
          Cancel Order
        </button>

        <Link href="/orders" class="px-4 py-2 bg-gray-900 text-white text-sm rounded-lg hover:bg-black transition">
          Back to Orders
        </Link>
      </div>
    </div>

    <div
      v-if="receiptModalOpen"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999] p-4"
      @click.self="closeReceiptModal"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative">
        <button @click="closeReceiptModal" class="absolute top-4 right-4 text-gray-400 hover:text-black text-xl">✕</button>

        <div class="p-6">
          <div v-if="receiptLoading" class="flex flex-col items-center justify-center py-10">
            <div class="w-10 h-10 border-4 border-gray-200 border-t-gray-900 rounded-full animate-spin mb-4"></div>
            <p class="text-sm text-gray-500">Loading receipt...</p>
          </div>

          <div v-else-if="receiptError" class="text-center text-red-500 py-10">
            {{ receiptError }}
          </div>

          <div v-else-if="receipt" class="flex justify-center">
            <div class="bg-white w-80 shadow-xl p-6 text-sm font-mono text-black relative border">
              <div class="text-center mb-4">
                <h2 class="text-lg font-bold tracking-wider">PARTY TRAY</h2>
                <p>F&b, 66 United Street, Mandaluyong</p>
                <p>--------------------------------</p>
              </div>

              <div class="mb-3">
                <p>Order #: {{ receipt.order_number }}</p>
                <p>Date: {{ receipt.created_at }}</p>
                <p>Name: {{ receipt.customer_name }}</p>
                <p>--------------------------------</p>
              </div>

              <div v-for="item in receipt.items" :key="item.id" class="mb-2">
                <div class="flex justify-between">
                  <span>{{ item.food_name || 'Deleted' }}</span>
                </div>
                <div class="flex justify-between">
                  <span>{{ item.quantity }} x PHP {{ formatMoney(item.price) }}</span>
                  <span>PHP {{ formatMoney(item.subtotal) }}</span>
                </div>
              </div>

              <p>--------------------------------</p>

              <div class="flex justify-between font-bold text-base mt-2">
                <span>TOTAL</span>
                <span>PHP {{ formatMoney(receipt.total) }}</span>
              </div>

              <p class="mt-3">--------------------------------</p>

              <div class="text-center mt-3">
                <p>Status: {{ String(receipt.status || '').replaceAll('_', ' ') }}</p>
                <p>Approval: {{ String(receipt.approval_status || 'pending').replaceAll('_', ' ') }}</p>
              </div>

              <div class="text-center mt-6 text-xs">
                <p>Thank you for your order!</p>
                <p>Please come again</p>
              </div>

              <div class="mt-6 text-center space-y-2">
                <button type="button" @click="printReceipt" class="bg-black text-white px-4 py-2 w-full rounded hover:bg-gray-800 transition">
                  Print
                </button>

                <a
                  :href="`/orders/${order.id}/receipt/download`"
                  class="block bg-green-600 text-white px-4 py-2 w-full rounded hover:bg-green-700 transition"
                >
                  Download
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
