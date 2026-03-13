<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({
  date: {
    type: String,
    required: true,
  },
  month: {
    type: String,
    default: '',
  },
  today: {
    type: String,
    default: '',
  },
  calendarCounts: {
    type: Object,
    default: () => ({}),
  },
  groups: {
    type: Object,
    default: () => ({
      data: [],
      links: [],
      total: 0,
    }),
  },
  totalOrdersForDay: {
    type: Number,
    default: 0,
  },
  storeHours: {
    type: Object,
    default: () => ({
      is_open: true,
      weekly: {},
    }),
  },
  flash: {
    type: Object,
    default: () => ({ success: null, error: null }),
  },
});

const selectedDate = ref(props.date);
const openScheduleSlots = ref({});
const controlsOpen = ref(false);
const overviewOpen = ref(true);
const editingControlDay = ref(null);
const activeTimeMenu = ref(null);
const weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const timeOptions = Array.from({ length: 48 }, (_, idx) => {
  const hours = String(Math.floor(idx / 2)).padStart(2, '0');
  const minutes = idx % 2 === 0 ? '00' : '30';

  return `${hours}:${minutes}`;
});

const monthTotalScheduled = computed(() =>
  Object.values(props.calendarCounts || {}).reduce((sum, n) => sum + Number(n || 0), 0)
);

const monthLabel = computed(() => {
  const source = String(props.month || props.date.slice(0, 7));
  const [year, month] = source.split('-').map(Number);

  return new Date(year, (month || 1) - 1, 1).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'long',
  });
});

const selectedPickerDate = computed(() => {
  if (!selectedDate.value) return null;
  return new Date(`${selectedDate.value}T00:00:00`);
});

const controlsForm = reactive({
  is_open: props.storeHours?.is_open ? 1 : 0,
  weekly: weekDays.reduce((carry, day) => {
    const source = props.storeHours?.weekly?.[day] || {};
    carry[day] = {
      enabled: source.enabled ? 1 : 0,
      open: source.open || '09:00',
      close: source.close || '17:00',
    };
    return carry;
  }, {}),
  processing: false,
});

function applyDateFilter() {
  router.get(
    '/admin/schedule',
    {
      date: selectedDate.value || undefined,
      month: String(selectedDate.value || props.month || '').slice(0, 7) || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['date', 'month', 'today', 'calendarCounts', 'groups', 'flash'],
    }
  );
}

function toDateKey(year, month, day) {
  const y = String(year);
  const m = String(month).padStart(2, '0');
  const d = String(day).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

function scheduleCountFor(date) {
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return 0;
  return Number(props.calendarCounts?.[toDateKey(date.getFullYear(), date.getMonth() + 1, date.getDate())] || 0);
}

function isToday(date) {
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return false;
  return toDateKey(date.getFullYear(), date.getMonth() + 1, date.getDate()) === props.today;
}

function onDatePicked(value) {
  if (!(value instanceof Date) || Number.isNaN(value.getTime())) return;
  selectedDate.value = toDateKey(value.getFullYear(), value.getMonth() + 1, value.getDate());
  applyDateFilter();
}

function saveControls() {
  controlsForm.processing = true;
  router.post(
    '/admin/schedule/controls',
    {
      is_open: controlsForm.is_open ? 1 : 0,
      weekly: weekDays.reduce((carry, day) => {
        carry[day] = {
          enabled: controlsForm.weekly[day].enabled ? 1 : 0,
          open: controlsForm.weekly[day].open,
          close: controlsForm.weekly[day].close,
        };
        return carry;
      }, {}),
    },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['storeHours', 'flash'],
      onFinish: () => {
        controlsForm.processing = false;
      },
    }
  );
}

function toggleEditDay(day) {
  editingControlDay.value = editingControlDay.value === day ? null : day;
}

function isEditingDay(day) {
  return editingControlDay.value === day;
}

function resetControls() {
  controlsForm.is_open = props.storeHours?.is_open ? 1 : 0;

  weekDays.forEach((day) => {
    const source = props.storeHours?.weekly?.[day] || {};
    controlsForm.weekly[day].enabled = source.enabled ? 1 : 0;
    controlsForm.weekly[day].open = source.open || '09:00';
    controlsForm.weekly[day].close = source.close || '17:00';
  });
}

function timeMenuKey(day, field) {
  return `${day}:${field}`;
}

function isTimeMenuOpen(day, field) {
  return activeTimeMenu.value === timeMenuKey(day, field);
}

function toggleTimeMenu(day, field) {
  const key = timeMenuKey(day, field);
  activeTimeMenu.value = activeTimeMenu.value === key ? null : key;
}

function selectTime(day, field, value) {
  controlsForm.weekly[day][field] = value;
  activeTimeMenu.value = null;
}

function handleDocumentClick(event) {
  const target = event.target;
  if (!(target instanceof Element)) return;
  if (!target.closest('[data-time-menu]')) {
    activeTimeMenu.value = null;
  }
}

function dayLabel(day) {
  return day.charAt(0).toUpperCase() + day.slice(1);
}

function formatTimeLabel(value) {
  const [hourText, minute = '00'] = String(value || '').split(':');
  const hour = Number(hourText);

  if (Number.isNaN(hour)) return value;

  const suffix = hour >= 12 ? 'PM' : 'AM';
  const normalizedHour = hour % 12 || 12;

  return `${normalizedHour}:${minute} ${suffix}`;
}

function formatSlotLabel(slot) {
  const [start, end] = String(slot || '').split('-');
  if (!start || !end) return slot;

  return `${formatTimeLabel(start)} - ${formatTimeLabel(end)}`;
}

function isSlotOpen(slot) {
  return openScheduleSlots.value[slot] ?? false;
}

function toggleSlot(slot) {
  openScheduleSlots.value = {
    ...openScheduleSlots.value,
    [slot]: !isSlotOpen(slot),
  };
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick);
});
</script>

<template>
  <Head title="Schedule" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-2xl font-bold tracking-tight text-green-600">Schedule</h1>
    </section>

    <section v-if="flash?.success" class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">
      {{ flash.success }}
    </section>
    <section v-if="flash?.error" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
      {{ flash.error }}
    </section>

    <section class="mb-6 rounded-2xl border bg-white p-4 shadow">
      <div class="flex flex-col gap-4 border-b border-gray-100 pb-4 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
          <h2 class="text-lg font-semibold text-gray-800">Operating Controls</h2>
          <p class="mt-1 text-xs text-gray-500">Manage store availability and keep daily opening hours.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <label class="inline-flex w-fit items-center gap-3 rounded-full border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700">
            <span
              class="inline-flex h-2.5 w-2.5 rounded-full"
              :class="controlsForm.is_open ? 'bg-green-500' : 'bg-gray-300'"
            ></span>
            <span>{{ controlsForm.is_open ? 'Store Open' : 'Store Closed' }}</span>
            <input v-model="controlsForm.is_open" :true-value="1" :false-value="0" type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
          </label>

          <button
            type="button"
            @click="controlsOpen = !controlsOpen"
            class="rounded-full border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ controlsOpen ? 'Hide' : 'Show' }}
          </button>
        </div>
      </div>

      <div v-if="controlsOpen" class="mt-4 space-y-2">
        <div
          v-for="day in weekDays"
          :key="day"
          class="rounded-xl border border-gray-200 bg-gray-50"
        >
          <div class="flex flex-col gap-3 px-3 py-3 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-800">{{ dayLabel(day) }}</p>
              <p class="mt-1 text-xs text-gray-500">
                {{ controlsForm.weekly[day].enabled ? `${formatTimeLabel(controlsForm.weekly[day].open)} - ${formatTimeLabel(controlsForm.weekly[day].close)}` : 'Closed' }}
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <span
                class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                :class="controlsForm.weekly[day].enabled ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
              >
                {{ controlsForm.weekly[day].enabled ? 'Open' : 'Closed' }}
              </span>
              <button
                type="button"
                @click="toggleEditDay(day)"
                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100"
              >
                {{ isEditingDay(day) ? 'Close' : 'Edit' }}
              </button>
            </div>
          </div>

          <div v-if="isEditingDay(day)" class="border-t border-gray-200 bg-white px-3 py-3">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-[90px_minmax(0,1fr)_minmax(0,1fr)] md:items-center">
              <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                <input v-model="controlsForm.weekly[day].enabled" :true-value="1" :false-value="0" type="checkbox" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                Open
              </label>

              <label class="block" data-time-menu>
                <span class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-500">Open</span>
                <div class="relative">
                  <button
                    type="button"
                    @click.stop="toggleTimeMenu(day, 'open')"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition hover:border-gray-400 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400"
                    :disabled="!controlsForm.weekly[day].enabled"
                  >
                    <span>{{ formatTimeLabel(controlsForm.weekly[day].open) }}</span>
                    <span class="text-xs text-gray-400">{{ isTimeMenuOpen(day, 'open') ? '▲' : '▼' }}</span>
                  </button>
                  <div
                    v-if="isTimeMenuOpen(day, 'open')"
                    class="mt-2 h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1"
                  >
                    <button
                      v-for="time in timeOptions"
                      :key="`${day}-open-${time}`"
                      type="button"
                      @click.stop="selectTime(day, 'open', time)"
                      class="block w-full px-3 py-2 text-left text-sm transition hover:bg-gray-50"
                      :class="controlsForm.weekly[day].open === time ? 'bg-green-50 font-medium text-green-700' : 'text-gray-700'"
                    >
                      {{ formatTimeLabel(time) }}
                    </button>
                  </div>
                </div>
              </label>

              <label class="block" data-time-menu>
                <span class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-gray-500">Close</span>
                <div class="relative">
                  <button
                    type="button"
                    @click.stop="toggleTimeMenu(day, 'close')"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition hover:border-gray-400 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400"
                    :disabled="!controlsForm.weekly[day].enabled"
                  >
                    <span>{{ formatTimeLabel(controlsForm.weekly[day].close) }}</span>
                    <span class="text-xs text-gray-400">{{ isTimeMenuOpen(day, 'close') ? '▲' : '▼' }}</span>
                  </button>
                  <div
                    v-if="isTimeMenuOpen(day, 'close')"
                    class="mt-2 h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1"
                  >
                    <button
                      v-for="time in timeOptions"
                      :key="`${day}-close-${time}`"
                      type="button"
                      @click.stop="selectTime(day, 'close', time)"
                      class="block w-full px-3 py-2 text-left text-sm transition hover:bg-gray-50"
                      :class="controlsForm.weekly[day].close === time ? 'bg-green-50 font-medium text-green-700' : 'text-gray-700'"
                    >
                      {{ formatTimeLabel(time) }}
                    </button>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div v-if="controlsOpen" class="mt-4 flex justify-end">
        <button
          type="button"
          @click="saveControls"
          :disabled="controlsForm.processing"
          class="rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-60"
        >
          {{ controlsForm.processing ? 'Saving...' : 'Save ' }}
        </button>
      </div>
    </section>

    <section class="mb-6 rounded-2xl border bg-white p-4 shadow">
      <div class="mb-4 flex flex-col gap-3 border-b border-gray-200 pb-4 md:flex-row md:items-start md:justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Schedule Overview</h2>
          <p class="mt-1 text-sm text-gray-500">{{ monthLabel }} • {{ monthTotalScheduled }} scheduled this month</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <span class="w-fit rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
            {{ groups.total || 0 }} {{ Number(groups.total || 0) === 1 ? 'slot' : 'slots' }} on {{ selectedDate }}
          </span>
          <button
            type="button"
            @click="overviewOpen = !overviewOpen"
            class="rounded-full border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ overviewOpen ? 'Hide' : 'Show' }}
          </button>
        </div>
      </div>

      <div v-if="overviewOpen" class="flex flex-col gap-6 lg:flex-row lg:items-start">
        <div class="lg:sticky lg:top-6 lg:w-[320px] lg:min-w-[320px] xl:w-[340px] xl:min-w-[340px]">
          <div class="schedule-picker rounded-[26px] border border-gray-200 bg-[#f7f7f5] p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-gray-100 pb-3">
              <div>
                <h3 class="text-base font-semibold text-gray-900">{{ monthLabel }}</h3>
                <p class="mt-1 text-xs text-gray-500">{{ monthTotalScheduled }} scheduled this month</p>
              </div>
              <div class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200">
                {{ selectedDate }}
              </div>
            </div>

            <div>
            <VueDatePicker
              :model-value="selectedPickerDate"
              inline
              auto-apply
              :enable-time-picker="false"
              :action-row="{ showPreview: false, showNow: false, showSelect: false, showCancel: false }"
              :formats="{ input: 'MM/dd/yyyy', preview: 'MM/dd/yyyy' }"
              :clearable="false"
              :month-change-on-scroll="false"
              @update:model-value="onDatePicked"
            >
              <template #day="{ day, date }">
                <div
                  class="flex min-h-[56px] w-full items-start rounded-xl px-2 py-2 text-left transition-colors duration-200"
                >
                  <span
                    class="flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-semibold"
                    :class="isToday(date) ? 'bg-green-100 text-green-700' : 'text-gray-800'"
                  >
                    {{ day }}
                  </span>
                </div>
              </template>
            </VueDatePicker>
          </div>
          </div>

          <p class="mt-3 text-xs text-gray-500">Pick a date to view the scheduled orders for that day.</p>
        </div>

        <div class="min-w-0 flex-1 border-t border-gray-200 pt-4 lg:border-t-0 lg:border-l lg:pl-6 lg:pt-0">
          <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">Orders</h3>
              <p class="mt-1 text-sm text-gray-500">Orders scheduled for {{ selectedDate }}</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
              {{ totalOrdersForDay }} {{ Number(totalOrdersForDay) === 1 ? 'order' : 'orders' }}
            </span>
          </div>

          <section v-if="groups.data?.length" class="space-y-4">
            <article v-for="group in groups.data" :key="group.slot" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
              <button
                type="button"
                @click="toggleSlot(group.slot)"
                class="flex w-full items-center justify-between gap-3 text-left"
              >
                <div>
                  <h3 class="font-semibold text-gray-800">Order for: {{ formatSlotLabel(group.slot) }}</h3>
                  <p class="mt-1 text-xs text-gray-500">
                    {{ group.orders.length }} {{ group.orders.length === 1 ? 'order' : 'orders' }}
                  </p>
                </div>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-sm text-gray-600 shadow-sm ring-1 ring-gray-200">
                  {{ isSlotOpen(group.slot) ? '−' : '+' }}
                </span>
              </button>

              <div v-if="isSlotOpen(group.slot)" class="mt-3 space-y-2">
                <div v-for="order in group.orders" :key="order.id" class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white p-3">
                  <div>
                    <p class="font-medium text-gray-800">#{{ order.order_number }}</p>
                    <p class="text-sm text-gray-600">{{ order.customer_name }}</p>
                  </div>
                  <div class="text-right">
                    <div class="mb-1 text-sm text-gray-600">{{ order.status_label }}</div>
                    <Link
                      :href="`/admin/orders?q=${encodeURIComponent(order.order_number)}`"
                      class="text-xs text-green-600 hover:underline"
                    >
                      View Order
                    </Link>
                  </div>
                </div>
              </div>
            </article>
          </section>

          <section v-else class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-500">
            No scheduled orders for this date.
          </section>

          <section v-if="groups.links?.length > 3" class="mt-5 rounded-xl border border-gray-200 bg-white p-3">
            <div class="flex flex-wrap items-center gap-2">
              <template v-for="(link, idx) in groups.links" :key="idx">
                <span
                  v-if="!link.url"
                  class="rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-400"
                  v-html="link.label"
                />
                <Link
                  v-else
                  :href="link.url"
                  class="rounded-lg border px-3 py-2 text-xs transition"
                  :class="link.active ? 'border-green-600 bg-green-600 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'"
                  preserve-state
                  preserve-scroll
                  :only="['date', 'month', 'today', 'calendarCounts', 'groups', 'flash']"
                  v-html="link.label"
                />
              </template>
            </div>
          </section>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<style scoped>
.schedule-picker :deep(.dp__main) {
  font-family: inherit;
}

.schedule-picker :deep(.dp__theme_light) {
  --dp-background-color: #ffffff;
  --dp-text-color: #111827;
  --dp-hover-color: #f0fdf4;
  --dp-hover-text-color: #166534;
  --dp-primary-color: #16a34a;
  --dp-primary-text-color: #ffffff;
  --dp-border-color: #d1d5db;
  --dp-menu-border-color: transparent;
  --dp-border-radius: 0.5rem;
  --dp-cell-border-radius: 0.5rem;
  --dp-cell-size: 40px;
  --dp-font-size: 0.875rem;
  --dp-common-padding: 6px;
  --dp-month-year-row-height: 48px;
}

.schedule-picker :deep(.dp__calendar_header) {
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.schedule-picker :deep(.dp__month_year_wrap),
.schedule-picker :deep(.dp__calendar_header_separator),
.schedule-picker :deep(.dp__month_year_row),
.schedule-picker :deep(.dp__calendar) {
  background: transparent;
}

.schedule-picker :deep(.dp__month_year_row) {
  margin-bottom: 0.5rem;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 0.5rem;
}

.schedule-picker :deep(.dp__menu) {
  border: 0;
  box-shadow: none;
}

.schedule-picker :deep(.dp__month_year_wrap) {
  align-items: center;
}

.schedule-picker :deep(.dp__month_year_select) {
  font-weight: 700;
  color: #111827;
  font-size: 1.05rem;
}

.schedule-picker :deep(.dp__month_year_select:hover) {
  background: transparent;
  color: #111827;
}

.schedule-picker :deep(.dp__inner_nav) {
  height: 36px;
  width: 36px;
  border-radius: 10px;
  background: #ffffff;
  color: #111827;
  box-shadow: inset 0 0 0 1px #e5e7eb;
}

.schedule-picker :deep(.dp__inner_nav:hover) {
  background: #f9fafb;
  color: #111827;
}

.schedule-picker :deep(.dp__calendar_row) {
  margin: 4px 0;
}

.schedule-picker :deep(.dp__calendar_item) {
  padding: 0 2px;
}

.schedule-picker :deep(.dp__cell_inner) {
  border-radius: 12px;
  font-weight: 600;
}

.schedule-picker :deep(.dp__cell_inner:hover) {
  background: transparent;
  color: inherit;
}

.schedule-picker :deep(.dp__date_hover),
.schedule-picker :deep(.dp__date_hover_start:hover),
.schedule-picker :deep(.dp__date_hover_end:hover),
.schedule-picker :deep(.dp__date_hover:hover) {
  background: transparent;
  color: inherit;
}

.schedule-picker :deep(.dp__active_date) {
  background: #16a34a;
  color: #ffffff;
  box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.1);
}

.schedule-picker :deep(.dp__today) {
  border-color: #cbd5e1;
}

.schedule-picker :deep(.dp__cell_offset) {
  opacity: 0.42;
  color: #94a3b8;
}

.schedule-picker :deep(.dp__calendar_header_item) {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
}

.schedule-picker :deep(.dp__calendar_header_cell) {
  padding-bottom: 0.25rem;
  border-bottom: 0;
}

.schedule-picker :deep(.dp__cell_disabled) {
  color: #cbd5e1;
}

.schedule-picker :deep(.dp__action_row),
.schedule-picker :deep(.dp__selection_preview),
.schedule-picker :deep(.dp__action_buttons),
.schedule-picker :deep(.dp__button_bottom),
.schedule-picker :deep([data-test-id="open-time-picker-btn"]) {
  display: none !important;
}
</style>
