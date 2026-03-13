<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      total_orders: 0,
      pending_orders: 0,
      completed_orders: 0,
    }),
  },
  recent_orders: {
    type: Array,
    default: () => [],
  },
  best_sellers: {
    type: Array,
    default: () => [],
  },
});

function money(value) {
  return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function statusClass(status) {
  return status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700';
}
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div>
      <section class="mb-6 sm:mb-12 rounded-3xl p-4 sm:p-10 text-white shadow-xl bg-gradient-to-r from-green-500 to-green-600">
        <h1 class="text-xl sm:text-3xl font-bold mb-2">Ready to eat?</h1>
        <p class="text-xs sm:text-sm opacity-90 mb-4 sm:mb-6">Your favorites are waiting.</p>
        <Link href="/" class="inline-block bg-white text-green-600 px-5 py-2.5 sm:px-6 sm:py-3 rounded-2xl text-sm sm:text-base font-semibold shadow-md hover:scale-105 transition duration-300">
          Order Now
        </Link>
      </section>

      <section class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8 sm:mb-12">
        <article class="bg-white rounded-2xl px-4 sm:px-8 py-4 sm:py-5 shadow-md flex items-center gap-3 sm:gap-4">
          <p class="text-3xl font-bold text-gray-900">{{ stats.total_orders }}</p>
          <p class="text-xs sm:text-sm text-gray-400 uppercase">Orders</p>
        </article>

        <article class="bg-white rounded-2xl px-4 sm:px-8 py-4 sm:py-5 shadow-md flex items-center gap-3 sm:gap-4">
          <p class="text-3xl font-bold text-yellow-500">{{ stats.pending_orders }}</p>
          <p class="text-xs sm:text-sm text-gray-400 uppercase">Pending</p>
        </article>

        <article class="bg-white rounded-2xl px-4 sm:px-8 py-4 sm:py-5 shadow-md flex items-center gap-3 sm:gap-4">
          <p class="text-3xl font-bold text-green-600">{{ stats.completed_orders }}</p>
          <p class="text-xs sm:text-sm text-gray-400 uppercase">Completed</p>
        </article>
      </section>

      <section class="mb-14">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 sm:mb-6">Your Orders</h2>

        <div v-if="recent_orders.length" class="space-y-4">
          <article
            v-for="order in recent_orders"
            :key="order.id"
            class="bg-white rounded-2xl shadow-md p-3 sm:p-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4 hover:shadow-xl transition"
          >
            <div>
              <p class="text-sm sm:text-base font-semibold text-gray-900">{{ order.order_number }}</p>
              <p class="text-[11px] sm:text-xs text-gray-400 mt-1">{{ order.created_at_label }}</p>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-4">
              <span class="text-xs px-4 py-1 rounded-full" :class="statusClass(order.status)">
                {{ order.status_label }}
              </span>

              <Link :href="`/orders/track/${order.order_number}`" class="text-green-600 font-semibold hover:scale-110 transition">
                →
              </Link>
            </div>
          </article>
        </div>

        <p v-else class="text-gray-400 text-sm">No orders yet.</p>
      </section>

      <section>
        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 sm:mb-6">Best Sellers</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5 sm:gap-8">
          <article
            v-for="food in best_sellers"
            :key="food.id"
            class="group bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition"
          >
            <div class="relative overflow-hidden">
              <img
                v-if="food.image_url"
                :src="food.image_url"
                :alt="food.name"
                class="w-full h-52 object-cover group-hover:scale-110 transition duration-700"
              />
              <div v-else class="w-full h-52 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
                No Image
              </div>
            </div>

            <div class="p-4 sm:p-6">
              <p class="text-sm sm:text-base font-semibold text-gray-900 group-hover:text-green-600 transition break-words">
                {{ food.name }}
              </p>
              <p class="text-xs sm:text-sm text-gray-400 mt-2">₱{{ money(food.price) }}</p>
              <Link href="/" class="mt-3 sm:mt-4 inline-block text-sm text-green-600 font-semibold hover:underline">
                Order →
              </Link>
            </div>
          </article>
        </div>
      </section>

    </div>
  </AppLayout>
</template>
