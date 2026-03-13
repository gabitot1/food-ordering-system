<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  cartItems: {
    type: Array,
    default: () => [],
  },
  deliveryFee: {
    type: Number,
    default: 50,
  },
  subtotal: {
    type: Number,
    default: 0,
  },
  grandTotal: {
    type: Number,
    default: 0,
  },
  minScheduleDate: {
    type: String,
    default: '',
  },
  storeHours: {
    type: Object,
    default: () => ({
      is_open: true,
      weekly: {},
    }),
  },
  storeStatus: {
    type: Object,
    default: () => ({ is_open_now: true, message: '' }),
  },
  flash: {
    type: Object,
    default: () => ({ success: null, error: null }),
  },
});

const isEmpty = computed(() => props.cartItems.length === 0);
const editModalOpen = ref(false);
const customerModalOpen = ref(false);
const editItemId = ref(null);
const editItemName = ref('');
const editItemInstruction = ref('');
const quantityDrafts = reactive({});
const removingItemId = ref(null);
const savingEditNote = ref(false);
const addingMoreItems = ref(false);
const accordionOpen = reactive({
  schedule: false,
  payment: false,
});

const form = useForm({
  customer_name: '',
  address: '',
  department: '',
  id_number: '',
  email: '',
  contact_number: '',
  instruction: '',
  delivery_option: 'pickup',
  payment_method: '',
  is_scheduled: false,
  scheduled_date: '',
  schedule_slot: '',
});
const canCheckoutNow = computed(() => Boolean(props.storeStatus?.is_open_now) || form.is_scheduled);
const disabledScheduleDates = computed(() => {
  if (props.storeStatus?.is_open_now) {
    return [];
  }

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  return [today];
});

const scheduledDatePickerValue = computed({
  get() {
    if (!form.scheduled_date) return null;
    return new Date(`${form.scheduled_date}T00:00:00`);
  },
  set(value) {
    if (!(value instanceof Date) || Number.isNaN(value.getTime())) {
      form.scheduled_date = '';
      return;
    }

    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');
    form.scheduled_date = `${year}-${month}-${day}`;
  },
});
const availableScheduleSlots = computed(() => {
  if (!form.scheduled_date) return [];

  const date = new Date(`${form.scheduled_date}T00:00:00`);
  if (Number.isNaN(date.getTime())) return [];
  const now = new Date();
  const isToday = date.toDateString() === now.toDateString();
  const currentMinutes = (now.getHours() * 60) + now.getMinutes();

  const dayKeys = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
  const dayKey = dayKeys[date.getDay()];
  const dayConfig = props.storeHours?.weekly?.[dayKey];

  if (!props.storeHours?.is_open || !dayConfig?.enabled) {
    return [];
  }

  const slots = [];
  let slotStartMinutes = toMinutes(dayConfig.open);
  const closeMinutes = toMinutes(dayConfig.close);

  if (slotStartMinutes === null || closeMinutes === null || slotStartMinutes >= closeMinutes) {
    return [];
  }

  while (slotStartMinutes < closeMinutes) {
    const nextMinutes = Math.min(slotStartMinutes + 120, closeMinutes);

    if (!isToday || slotStartMinutes > currentMinutes) {
      slots.push({
        value: `${toTimeValue(slotStartMinutes)}-${toTimeValue(nextMinutes)}`,
        label: `${formatMinutes(slotStartMinutes)} - ${formatMinutes(nextMinutes)}`,
      });
    }

    slotStartMinutes = nextMinutes;
  }

  return slots;
});
const selectedScheduleDayLabel = computed(() => {
  if (!form.scheduled_date) return '';

  const date = new Date(`${form.scheduled_date}T00:00:00`);
  if (Number.isNaN(date.getTime())) return '';

  return date.toLocaleDateString(undefined, { weekday: 'long' });
});

const clientErrors = reactive({
  customer_name: '',
  address: '',
  department: '',
  id_number: '',
  email: '',
  contact_number: '',
  payment_method: '',
  scheduled_date: '',
  schedule_slot: '',
});

watch(
  () => accordionOpen.schedule,
  (enabled) => {
    form.is_scheduled = enabled;

    if (!enabled) {
      form.scheduled_date = '';
      form.schedule_slot = '';
    }
  }
);

watch(
  () => form.scheduled_date,
  () => {
    if (!availableScheduleSlots.value.some((slot) => slot.value === form.schedule_slot)) {
      form.schedule_slot = '';
    }
  }
);

watch(
  () => props.cartItems,
  (items) => {
    items.forEach((item) => {
      quantityDrafts[item.id] = Number(item.quantity || 1);
    });
  },
  { immediate: true }
);

function toMoney(value) {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function toMinutes(value) {
  const [hours, minutes] = String(value || '').split(':').map(Number);
  if (Number.isNaN(hours) || Number.isNaN(minutes)) return null;
  return (hours * 60) + minutes;
}

function toTimeValue(totalMinutes) {
  const hours = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
  const minutes = String(totalMinutes % 60).padStart(2, '0');
  return `${hours}:${minutes}`;
}

function formatMinutes(totalMinutes) {
  const hours = Math.floor(totalMinutes / 60);
  const minutes = String(totalMinutes % 60).padStart(2, '0');
  const suffix = hours >= 12 ? 'PM' : 'AM';
  const normalizedHour = hours % 12 || 12;
  return `${normalizedHour}:${minutes} ${suffix}`;
}

function formatSchedulePickerDate(value) {
  if (!(value instanceof Date) || Number.isNaN(value.getTime())) {
    return '';
  }

  return value.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  });
}

function increase(itemId) {
  router.post(`/cart/increase/${itemId}`, {}, { preserveScroll: true });
}

function decrease(itemId) {
  router.post(`/cart/decrease/${itemId}`, {}, { preserveScroll: true });
}

function removeItem(itemId) {
  removingItemId.value = itemId;
  router.delete(`/cart/remove/${itemId}`, {
    preserveScroll: true,
    onFinish: () => {
      removingItemId.value = null;
    },
  });
}

function updateQuantity(item) {
  const quantity = Math.max(1, Number(quantityDrafts[item.id] || 1));
  quantityDrafts[item.id] = quantity;

  router.post(
    `/cart/quantity/${item.id}`,
    { quantity },
    {
      preserveScroll: true,
    }
  );
}

function clampQuantityInput(item) {
  const maxQty = item.available_quantity === null ? null : Number(item.available_quantity);
  const parsedQuantity = Math.max(1, Number(quantityDrafts[item.id] || 1));
  quantityDrafts[item.id] = maxQty === null ? parsedQuantity : Math.min(parsedQuantity, Math.max(1, maxQty));
}

function openEditInstruction(item) {
  editItemId.value = item.id;
  editItemName.value = item.name || 'Item';
  editItemInstruction.value = item.instruction || '';
  editModalOpen.value = true;
  syncBodyScrollLock();
}

function closeEditInstruction() {
  editModalOpen.value = false;
  syncBodyScrollLock();
}

function syncBodyScrollLock() {
  document.body.classList.toggle('overflow-hidden', editModalOpen.value || customerModalOpen.value);
}

function openCustomerDetailsModal() {
  customerModalOpen.value = true;
  syncBodyScrollLock();
}

function closeCustomerDetailsModal() {
  customerModalOpen.value = false;
  syncBodyScrollLock();
}

async function continueFromCustomerDetails() {
  const valid = validateCheckout();
  if (!valid) return;

  if (!canCheckoutNow.value) {
    if (window.Swal) {
      await window.Swal.fire({
        icon: 'warning',
        title: 'Store is closed',
        text: props.storeStatus?.message || 'Store is currently closed.',
        confirmButtonColor: '#16a34a',
      });
    }
    return;
  }

  closeCustomerDetailsModal();
  submitCheckout();
}

function saveEditInstruction() {
  if (!editItemId.value || savingEditNote.value) return;
  savingEditNote.value = true;

  router.post(
    `/cart/instruction/${editItemId.value}`,
    { instruction: String(editItemInstruction.value || '').trim() || null },
    {
      preserveScroll: true,
      onSuccess: () => {
        closeEditInstruction();
      },
      onFinish: () => {
        savingEditNote.value = false;
      },
    }
  );
}

function addMoreItems() {
  if (addingMoreItems.value) return;
  addingMoreItems.value = true;

  router.get('/menu', {}, {
    onFinish: () => {
      addingMoreItems.value = false;
    },
  });
}

function clearClientErrors() {
  Object.keys(clientErrors).forEach((key) => {
    clientErrors[key] = '';
  });
}

function toggleAccordion(section) {
  accordionOpen[section] = !accordionOpen[section];
}

function deliveryLabel(value) {
  return value === 'delivery' ? 'Delivery' : 'Pick-up';
}

function paymentLabel(value) {
  return ({
    cash: 'Cash on Delivery',
    gcash: 'GCash',
    card: 'Credit / Debit Card',
  })[value] || 'Select payment method';
}

function fieldError(name) {
  return clientErrors[name] || form.errors[name];
}

function validateCheckout() {
  clearClientErrors();

  const compactContact = String(form.contact_number || '').replace(/[^\d+]/g, '');

  if (!String(form.customer_name || '').trim()) clientErrors.customer_name = 'Full Name is required.';
  if (!String(form.address || '').trim()) clientErrors.address = 'Address is required.';
  if (!String(form.department || '').trim()) clientErrors.department = 'Division is required.';
  if (!String(form.id_number || '').trim()) clientErrors.id_number = 'ID Number is required.';
  if (!String(form.email || '').trim()) {
    clientErrors.email = 'Email is required.';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(form.email || '').trim())) {
    clientErrors.email = 'Email format is invalid.';
  }
  if (!String(form.contact_number || '').trim()) {
    clientErrors.contact_number = 'Contact Number is required.';
  } else if (!/^\+?\d{10,15}$/.test(compactContact)) {
    clientErrors.contact_number = 'Contact Number must be 10 to 15 digits.';
  }
  if (!String(form.payment_method || '').trim()) clientErrors.payment_method = 'Please select a payment method.';

  if (form.is_scheduled) {
    if (!String(form.scheduled_date || '').trim()) clientErrors.scheduled_date = 'Schedule date is required.';
    if (!String(form.schedule_slot || '').trim()) clientErrors.schedule_slot = 'Schedule slot is required.';
  }

  return Object.values(clientErrors).every((message) => !message);
}

function submitCheckout() {
  form.transform((data) => ({
    ...data,
    is_scheduled: data.is_scheduled ? 1 : 0,
  })).post('/checkout', {
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Cart" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-2xl font-semibold text-gray-800 tracking-wide">Cart</h1>
    </section>

    <!-- <section v-if="flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
      {{ flash.success }}
    </section> -->
    <section v-if="flash?.error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
      {{ flash.error }}
    </section>
    <section
      class="mb-4 p-3 rounded-xl text-sm border"
      :class="storeStatus?.is_open_now ? 'bg-green-50 border-green-200 text-green-800' : 'bg-amber-50 border-amber-200 text-amber-800'"
    >
      {{ storeStatus?.message || (storeStatus?.is_open_now ? 'Store is open now.' : 'Store is currently closed.') }}
    </section>

    <div v-if="isEmpty" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-16 text-center">
      <div class="text-5xl mb-4">🛒</div>
      <h3 class="text-lg font-medium text-gray-700">Your cart is empty</h3>
      <Link href="/menu" class="inline-block mt-4 text-sm text-green-700 hover:underline">Go to menu</Link>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-10">
      <div class="lg:col-span-2 space-y-5">
        <article
          v-for="item in cartItems"
          :key="item.id"
          class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition"
        >
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div class="flex items-start gap-4">
              <img
                v-if="item.image_url"
                :src="item.image_url"
                :alt="item.name"
                class="w-16 h-16 rounded-xl object-cover border border-gray-200"
              >
              <div class="w-16 h-16 rounded-xl border border-gray-200 bg-gray-100" v-else></div>

              <div>
                <h4 class="text-base font-semibold text-gray-900">{{ item.name }}</h4>
                <p v-if="item.description" class="text-xs text-gray-500 mt-1 max-w-md line-clamp-2">{{ item.description }}</p>
                <p class="text-sm text-gray-500 mt-1">₱{{ toMoney(item.price) }}</p>
                <p v-if="item.instruction" class="text-sm text-gray-500 mt-1 italic">Note: {{ item.instruction }}</p>
              </div>
            </div>

            <div class="flex min-w-[140px] items-center justify-center gap-3 self-start sm:self-center">
              <button
                type="button"
                @click="decrease(item.id)"
                :disabled="item.quantity <= 1"
                class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-green-700 hover:text-white transition"
              >
                −
              </button>
              <input
                v-model="quantityDrafts[item.id]"
                type="number"
                min="1"
                :max="item.available_quantity ?? undefined"
                class="h-9 w-14 rounded-lg border border-gray-300 text-center text-sm font-medium text-gray-700 outline-none transition focus:border-green-600"
                @input="clampQuantityInput(item)"
                @blur="updateQuantity(item)"
                @keyup.enter="updateQuantity(item)"
              >
              
              <button
                type="button"
                @click="increase(item.id)"
                :disabled="item.available_quantity !== null && item.quantity >= item.available_quantity"
                class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-green-700 hover:text-white transition"
              >
              
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"/>
                  <path d="M12 5v14"/>
                </svg>
              </button>
            </div>

            <div class="text-right">
              <p class="font-semibold text-gray-900">₱{{ toMoney(item.price * item.quantity) }}</p>

              <div class="mt-2 flex items-center justify-end gap-2">
                <button
                  type="button"
                  @click="addMoreItems"
                  :disabled="addingMoreItems"
                  class="text-green-600 hover:text-green-700"
                  title="Add more items"
                >
                  <svg
                    v-if="addingMoreItems"
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    class="animate-spin"
                  >
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="lucide lucide-plus-icon lucide-plus"
                  >
                    <path d="M5 12h14"/>
                    <path d="M12 5v14"/>
                  </svg>
                </button>

                <button
                  type="button"
                  @click="openEditInstruction(item)"
                  class="text-gray-500 hover:text-gray-700"
                  title="Edit note"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                    <path d="m15 5 4 4"/>
                  </svg>
                </button>

                <button
                  type="button"
                  @click="removeItem(item.id)"
                  :disabled="removingItemId === item.id"
                  class="text-red-500 hover:text-red-700"
                  title="Remove item"
                >
                  <svg
                    v-if="removingItemId === item.id"
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    class="animate-spin"
                  >
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                    <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="lucide lucide-trash-2"
                  >
                    <path d="M10 11v6"/>
                    <path d="M14 11v6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                    <path d="M3 6h18"/>
                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </article>
      </div>

      <aside class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-7 sticky top-24">
          <h3 class="text-lg font-semibold text-gray-900 mb-5">Order Summary</h3>

          <div class="space-y-3 text-sm text-gray-600 mb-6">
            <div class="flex justify-between">
              <span>Subtotal</span>
              <span>₱{{ toMoney(subtotal) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Delivery Fee</span>
              <span>₱{{ toMoney(deliveryFee) }}</span>
            </div>
            <div class="border-t pt-3 flex justify-between font-semibold text-gray-900">
              <span>Total</span>
              <span>₱{{ toMoney(grandTotal) }}</span>
            </div>
          </div>

          <form @submit.prevent="openCustomerDetailsModal" class="space-y-4">
            <div class="space-y-2">
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <p class="text-xs font-semibold uppercase tracking-wide text-gray-700">Delivery</p>
                  <p class="text-xs text-gray-500">{{ deliveryLabel(form.delivery_option) }}</p>
                </div>
                <div class="flex overflow-hidden rounded-xl border border-gray-200">
                  <label
                    class="flex flex-1 cursor-pointer items-center justify-center gap-2 px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50"
                  >
                    <input v-model="form.delivery_option" type="radio" name="delivery_option" value="pickup" class="h-4 w-4 accent-green-600">
                    <span>Pick-up</span>
                  </label>
                  <label
                    class="flex flex-1 cursor-pointer items-center justify-center gap-2 border-l border-gray-200 px-3 py-3 text-sm text-gray-700 transition hover:bg-gray-50"
                  >
                    <input v-model="form.delivery_option" type="radio" name="delivery_option" value="delivery" class="h-4 w-4 accent-green-600">
                    <span>Delivery</span>
                  </label>
                </div>
                <p v-if="form.errors.delivery_option" class="text-xs text-red-600">{{ form.errors.delivery_option }}</p>
              </div>

              <div class="rounded-lg border border-gray-200">
                <button
                  type="button"
                  @click="toggleAccordion('schedule')"
                  class="flex w-full items-center justify-between bg-gray-50 px-3 py-2.5 text-left"
                >
                  <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-700">Schedule</p>
                    <p class="text-xs text-gray-500">
                      {{ form.is_scheduled ? (form.scheduled_date || 'Select date') : 'Order now' }}
                    </p>
                  </div>
                  <span class="text-sm text-gray-500">{{ accordionOpen.schedule ? '−' : '+' }}</span>
                </button>
                <div v-if="accordionOpen.schedule" class="space-y-2 border-t border-gray-200 p-2.5">
                  <div class="cart-schedule-picker">
                    <VueDatePicker
                      v-model="scheduledDatePickerValue"
                      name="scheduled_date"
                      :min-date="minScheduleDate ? new Date(`${minScheduleDate}T00:00:00`) : null"
                      :disabled-dates="disabledScheduleDates"
                      :enable-time-picker="false"
                      :action-row="{ showPreview: false, showNow: false, showSelect: false, showCancel: false }"
                      :formats="{ input: 'MM/dd/yyyy', preview: 'MM/dd/yyyy' }"
                      :format="formatSchedulePickerDate"
                      auto-apply
                      :clearable="false"
                      input-class-name="cart-schedule-input"
                      placeholder="Select schedule date"
                    />
                  </div>
                  <p v-if="fieldError('scheduled_date')" class="text-xs text-red-600">{{ fieldError('scheduled_date') }}</p>

                  <select v-model="form.schedule_slot" name="schedule_slot" class="w-full border border-gray-300 rounded-md px-3 py-2 cursor-pointer hover:border-gray-900 text-sm" :disabled="!availableScheduleSlots.length">
                    <option value="">Select time slot</option>
                    <option v-for="slot in availableScheduleSlots" :key="slot.value" :value="slot.value">{{ slot.label }}</option>
                  </select>
                  <p v-if="form.scheduled_date && !availableScheduleSlots.length" class="text-xs text-amber-600">
                    {{ selectedScheduleDayLabel || 'Selected day' }} is closed or has no available schedule slots.
                  </p>
                  <p v-if="fieldError('schedule_slot')" class="text-xs text-red-600">{{ fieldError('schedule_slot') }}</p>
                </div>
              </div>

              <div class="rounded-lg border border-gray-200">
                <button
                  type="button"
                  @click="toggleAccordion('payment')"
                  class="flex w-full items-center justify-between bg-gray-50 px-3 py-2.5 text-left"
                >
                  <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-700">Payment</p>
                    <p class="text-xs text-gray-500">{{ paymentLabel(form.payment_method) }}</p>
                  </div>
                  <span class="text-sm text-gray-500">{{ accordionOpen.payment ? '−' : '+' }}</span>
                </button>
                <div v-if="accordionOpen.payment" class="space-y-2 border-t border-gray-200 p-2.5">
                  <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer hover:border-gray-900 transition">
                    <input v-model="form.payment_method" type="radio" name="payment_method" value="cash">
                    <span class="text-sm">Cash on Delivery</span>
                  </label>
                  <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer hover:border-gray-900 transition">
                    <input v-model="form.payment_method" type="radio" name="payment_method" value="gcash">
                    <span class="text-sm">GCash</span>
                  </label>
                  <label class="flex items-center gap-2 border rounded-md px-3 py-2 cursor-pointer hover:border-gray-900 transition">
                    <input v-model="form.payment_method" type="radio" name="payment_method" value="card">
                    <span class="text-sm">Credit / Debit Card</span>
                  </label>
                  <p v-if="fieldError('payment_method')" class="text-xs text-red-600 mt-1">{{ fieldError('payment_method') }}</p>
                </div>
              </div>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gray-900 py-2.5 text-sm font-medium text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:opacity-60 sm:py-3"
            >
              <svg
                v-if="form.processing"
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                class="animate-spin"
              >
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
              </svg>
              {{ form.processing ? 'Processing...' : 'Proceed Order' }}
            </button>
          </form>
        </div>
      </aside>
    </div>

    <div
      v-if="customerModalOpen"
      class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50"
      @click.self="closeCustomerDetailsModal"
    >
      <div class="relative mx-4 w-full max-w-xl rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <button @click="closeCustomerDetailsModal" class="absolute right-4 top-4 text-xl text-gray-500 hover:text-black">✕</button>

        <h3 class="mb-1 text-xl font-bold text-gray-900">Customer Details</h3>
        <p class="mb-5 text-sm text-gray-600">Enter your information before placing the order.</p>

        <div class="space-y-4">
          <div>
            <input v-model="form.customer_name" type="text" name="customer_name" placeholder="Full Name" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition hover:border-gray-900">
            <p v-if="fieldError('customer_name')" class="mt-1 text-xs text-red-600">{{ fieldError('customer_name') }}</p>
          </div>

          <div>
            <input v-model="form.address" type="text" name="address" placeholder="Department" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition hover:border-gray-900">
            <p v-if="fieldError('address')" class="mt-1 text-xs text-red-600">{{ fieldError('address') }}</p>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <input v-model="form.department" type="text" name="department" placeholder="Division" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition hover:border-gray-900">
              <p v-if="fieldError('department')" class="mt-1 text-xs text-red-600">{{ fieldError('department') }}</p>
            </div>

            <div>
              <input v-model="form.id_number" type="text" name="id_number" placeholder="ID Number" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition hover:border-gray-900">
              <p v-if="fieldError('id_number')" class="mt-1 text-xs text-red-600">{{ fieldError('id_number') }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <input v-model="form.email" type="email" name="email" placeholder="Email" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition hover:border-gray-900">
              <p v-if="fieldError('email')" class="mt-1 text-xs text-red-600">{{ fieldError('email') }}</p>
            </div>

            <div>
              <input v-model="form.contact_number" type="text" name="contact_number" placeholder="Contact Number" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition hover:border-gray-900">
              <p v-if="fieldError('contact_number')" class="mt-1 text-xs text-red-600">{{ fieldError('contact_number') }}</p>
            </div>
          </div>

          <div>
            <textarea v-model="form.instruction" name="instruction" rows="4" placeholder="Special instructions (optional)" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition hover:border-gray-900"></textarea>
          </div>

          <div class="flex items-center gap-3">
            <button
              type="button"
              @click="closeCustomerDetailsModal"
              class="w-full rounded-xl border border-gray-300 py-2.5 text-sm font-medium transition hover:bg-gray-100"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="continueFromCustomerDetails"
              :disabled="form.processing"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 py-2.5 text-sm font-medium text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
              <svg
                v-if="form.processing"
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                class="animate-spin"
              >
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
              </svg>
              {{ form.processing ? 'Submitting...' : 'Continue' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="editModalOpen"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-[10000]"
      @click.self="closeEditInstruction"
    >
      <div class="relative bg-white w-full mx-4 max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl p-5 sm:p-6">
        <button @click="closeEditInstruction" class="absolute top-4 right-4 text-gray-500 hover:text-black text-xl">✕</button>

        <h3 class="text-xl font-bold mb-2">Edit Item Note</h3>
        <p class="text-sm text-gray-600 mb-4">{{ editItemName }}</p>

        <textarea
          v-model="editItemInstruction"
          placeholder="Take note / Special instruction (optional)"
          class="w-full border rounded-lg p-2 text-sm mb-3"
          rows="4"
        ></textarea>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="closeEditInstruction"
            :disabled="savingEditNote"
            class="w-full border border-gray-300 py-2 rounded-xl hover:bg-gray-100 transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="saveEditInstruction"
            :disabled="savingEditNote"
            class="w-full bg-green-600 text-white py-2 rounded-xl hover:bg-green-800 transition disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
          >
            <svg
              v-if="savingEditNote"
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              class="animate-spin"
            >
              <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
              <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            </svg>
            {{ savingEditNote ? 'Saving...' : 'Save Note' }}
          </button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<style scoped>
.cart-schedule-picker :deep(.dp__theme_light) {
  --dp-border-color: #d1d5db;
  --dp-border-radius: 0.5rem;
  --dp-font-size: 0.875rem;
  --dp-primary-color: #16a34a;
  --dp-primary-text-color: #ffffff;
  --dp-hover-color: #f0fdf4;
  --dp-hover-text-color: #166534;
}

.cart-schedule-picker :deep(.cart-schedule-input) {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  color: #111827;
}

.cart-schedule-picker :deep(.cart-schedule-input:hover) {
  border-color: #111827;
}

.cart-schedule-picker :deep(.cart-schedule-input:focus) {
  outline: none;
  border-color: #16a34a;
  box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.1);
}

.cart-schedule-picker :deep(.dp__action_row) {
  display: none;
}

.cart-schedule-picker :deep(.dp__selection_preview),
.cart-schedule-picker :deep(.dp__action_buttons),
.cart-schedule-picker :deep(.dp__button_bottom),
.cart-schedule-picker :deep([data-test-id="open-time-picker-btn"]) {
  display: none !important;
}
</style>
