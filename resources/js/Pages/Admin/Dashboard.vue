<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  stats: {
    type: Object,
    required: true,
  },
  sales: {
    type: Object,
    required: true,
  },
  top_foods: {
    type: Array,
    default: () => [],
  },
});

const maxDaily = computed(() => Math.max(...(props.sales.totals || [0]), 1));
const maxMonthly = computed(() => Math.max(...(props.sales.monthly_sales || [0]), 1));

function pct(value, maxValue) {
  return `${Math.max(0, Math.min(100, (Number(value || 0) / Number(maxValue || 1)) * 100))}%`;
}

function money(value) {
  return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<template>
  <Head title="Admin Dashboard" />

  <AppLayout>
    <div class="min-h-screen bg-white px-4 py-12 sm:px-4 sm:py-12">
      <div class="mx-auto max-w-7xl space-y-5 sm:space-y-8">
      <section>
        <h1 class="text-2xl font-bold text-green-600 tracking-tight">Admin Dashboard</h1>
      </section>

      <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="h-full rounded-2xl border border-white/40 bg-white/90 p-4 shadow-md backdrop-blur-lg transition-all duration-300 hover:shadow-green-400/30 sm:p-6 sm:shadow-2xl">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total Orders</p>
          <p class="text-2xl sm:text-3xl font-bold mt-4 text-gray-900">{{ stats.total_orders }}</p>
        </div>
        <div class="h-full rounded-2xl border border-white/40 bg-white/90 p-4 shadow-md backdrop-blur-lg transition-all duration-300 hover:shadow-green-400/30 sm:p-6 sm:shadow-2xl">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Total Sales</p>
          <p class="text-2xl sm:text-3xl font-bold mt-4 text-green-600">₱{{ money(stats.total_sales) }}</p>
        </div>
        <div class="h-full rounded-2xl border border-white/40 bg-white/90 p-4 shadow-md backdrop-blur-lg transition-all duration-300 hover:shadow-green-400/30 sm:p-6 sm:shadow-2xl">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Today Orders</p>
          <p class="text-2xl sm:text-3xl font-bold mt-4 text-gray-900">{{ stats.today_orders }}</p>
        </div>
        <div class="h-full rounded-2xl border border-white/40 bg-white/90 p-4 shadow-md backdrop-blur-lg transition-all duration-300 hover:shadow-green-400/30 sm:p-6 sm:shadow-2xl">
          <p class="text-xs text-gray-500 uppercase tracking-wide">Today Sales</p>
          <p class="text-2xl sm:text-3xl font-bold mt-4 text-indigo-900">₱{{ money(stats.today_sales) }}</p>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-4 md:grid-cols-2 sm:gap-6">
        <div class="rounded-3xl border border-white/40 bg-white/90 p-6 text-gray-800 shadow-2xl backdrop-blur-lg sm:p-7">
          <p class="text-xs uppercase tracking-wide opacity-80">Pending Orders</p>
          <p class="text-3xl sm:text-4xl font-bold mt-4 text-yellow-600">{{ stats.pending_count }}</p>
        </div>
        <div class="rounded-3xl border border-white/40 bg-white/90 p-6 text-gray-800 shadow-2xl backdrop-blur-lg sm:p-7">
          <p class="text-xs uppercase tracking-wide opacity-80">Preparing Orders</p>
          <p class="text-3xl sm:text-4xl font-bold mt-4 text-blue-600">{{ stats.preparing_count }}</p>
        </div>
      </section>

      <section class="grid grid-cols-1 gap-4 lg:grid-cols-2 sm:gap-8">
        <div class="rounded-3xl border border-white/40 bg-white/90 p-6 shadow-2xl backdrop-blur-lg sm:p-7">
          <h2 class="text-lg font-semibold text-gray-700 mb-6">Sales (Last 7 Days)</h2>
          <div class="space-y-3">
            <div v-for="(label, idx) in sales.days" :key="label" class="space-y-1">
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ label }}</span>
                <span>₱{{ money(sales.totals[idx]) }}</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div class="bg-indigo-600 h-2.5 rounded-full" :style="{ width: pct(sales.totals[idx], maxDaily) }"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-white/40 bg-white/90 p-6 shadow-2xl backdrop-blur-lg sm:p-7">
          <h2 class="text-lg font-semibold text-gray-700 mb-6">Monthly Sales (Last 6 Months)</h2>
          <div class="space-y-3">
            <div v-for="(label, idx) in sales.monthly_labels" :key="label" class="space-y-1">
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ label }}</span>
                <span>₱{{ money(sales.monthly_sales[idx]) }}</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div class="bg-indigo-600 h-2.5 rounded-full" :style="{ width: pct(sales.monthly_sales[idx], maxMonthly) }"></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-3xl border border-white/40 bg-white/90 p-6 shadow-2xl backdrop-blur-lg sm:p-7">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-xl font-semibold text-gray-800">Best Sellers</h2>
          <Link href="/admin/foods" class="text-sm text-green-600 hover:underline">Manage Foods →</Link>
        </div>

        <div v-if="!top_foods.length" class="text-center py-12 text-gray-400">
          No data available.
        </div>

        <div v-else class="space-y-6">
          <div v-for="row in top_foods" :key="row.food_id" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <p class="truncate text-base font-semibold text-gray-800">{{ row.name }}</p>
              <p class="text-xs text-gray-400 mt-1">Total Sold: {{ row.total_qty }}</p>
            </div>
            <div class="w-full sm:w-48 bg-gray-100 rounded-full h-2.5 sm:h-3">
              <div class="bg-indigo-600 h-2.5 sm:h-3 rounded-full" :style="{ width: `${row.bar_width}%` }"></div>
            </div>
          </div>
        </div>
      </section>
      </div>
    </div>
  </AppLayout>
</template>
