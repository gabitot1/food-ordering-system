<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
  order: {
    type: Object,
    required: true,
  },
  flash: {
    type: Object,
    default: () => ({ success: null }),
  },
});
</script>

<template>
  <Head title="Order Tracking" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-lg sm:text-2xl font-semibold text-green-800">Order Tracking</h1>
    </section>

    <!-- <section v-if="flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
      {{ flash.success }}
    </section> -->

    <div class="max-w-4xl mx-auto py-4 sm:py-10 px-0 sm:px-0">
      <div class="bg-white shadow rounded-2xl p-3 sm:p-6">
        <h3 class="text-sm sm:text-lg font-bold mb-3 sm:mb-4 break-all sm:break-normal">
          Order #: {{ order.order_number }}
        </h3>

        <div class="space-y-2 sm:space-y-3 text-xs sm:text-base text-gray-700">
          <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
            <span class="font-medium">Name</span>
            <span class="sm:text-right break-words">{{ order.customer_name }}</span>
          </div>

          <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
            <span class="font-medium">Total</span>
            <span class="font-semibold text-green-700 sm:text-right">
              ₱{{ Number(order.total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </span>
          </div>

          <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center">
            <span class="font-medium">Order Status</span>
            <span class="px-3 py-1 text-[11px] sm:text-xs rounded-full bg-blue-100 text-blue-700 w-fit">
              {{ order.status_label }}
            </span>
          </div>

          <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center">
            <span class="font-medium">Approval</span>
            <span
              class="px-3 py-1 text-[11px] sm:text-xs rounded-full w-fit"
              :class="order.approval_status === 'approved'
                ? 'bg-green-100 text-green-700'
                : order.approval_status === 'disapproved'
                  ? 'bg-red-100 text-red-700'
                  : 'bg-amber-100 text-amber-700'"
            >
              {{ order.approval_status_label }}
            </span>
          </div>

          <div v-if="order.approval_note" class="flex flex-col gap-1 sm:flex-row sm:justify-between">
            <span class="font-medium">Admin Note</span>
            <span class="sm:text-right text-red-600">{{ order.approval_note }}</span>
          </div>

          <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center">
            <span class="font-medium">Payment Status</span>
            <span
              class="px-3 py-1 text-[11px] sm:text-xs rounded-full w-fit"
              :class="order.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              {{ order.payment_status }}
            </span>
          </div>

          <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
            <span class="font-medium">Delivery</span>
            <span class="sm:text-right capitalize">{{ order.delivery_option }}</span>
          </div>

          <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
            <span class="font-medium">Date</span>
            <span class="sm:text-right">{{ order.created_at_label }}</span>
          </div>
        </div>

        <div class="mt-4">
          <Link
            href="/orders"
            class="inline-flex w-full sm:w-auto justify-center mt-4 sm:mt-6 bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm rounded-xl transition"
          >
            Back to Orders
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
