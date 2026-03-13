<script setup>
import { onBeforeUnmount, onMounted, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const POLL_INTERVAL_MS = 4000;
let pollTimer = null;

const props = defineProps({
  foods: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ q: '', availability: '' }),
  },
  flash: {
    type: Object,
    default: () => ({ success: null, error: null }),
  },
});

const form = reactive({
  q: props.filters.q || '',
  availability: props.filters.availability || '',
});

function search() {
  router.get('/admin/foods', {
    q: form.q || undefined,
    availability: form.availability || undefined,
  }, { preserveState: true, replace: true });
}

function clearFilters() {
  form.q = '';
  form.availability = '';
  router.get('/admin/foods', {}, { preserveState: true, replace: true });
}

async function toggleAvailability(id, isAvailable) {
  const result = await window.Swal.fire({
    title: isAvailable ? 'Mark as unavailable?' : 'Mark as available?',
    text: isAvailable
      ? 'This food will be hidden from available items.'
      : 'This food will be available for ordering.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#16a34a',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Confirm',
    cancelButtonText: 'Cancel',
  });

  if (!result.isConfirmed) return;

  router.patch(`/admin/foods/${id}/toggle`, {}, { preserveScroll: true });
}

async function updateApproval(food, approvalStatus) {
  let rejectionReason = '';

  if (approvalStatus === 'rejected') {
    const reasonResult = await window.Swal.fire({
      title: 'Reject food?',
      input: 'textarea',
      inputLabel: 'Rejection reason',
      inputPlaceholder: 'Enter the reason for rejection',
      inputAttributes: {
        'aria-label': 'Rejection reason',
      },
      showCancelButton: true,
      confirmButtonText: 'Reject',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      inputValidator: (value) => {
        if (!String(value || '').trim()) {
          return 'Rejection reason is required.';
        }

        return null;
      },
    });

    if (!reasonResult.isConfirmed) return;
    rejectionReason = String(reasonResult.value || '').trim();
  } else {
    const result = await window.Swal.fire({
      title: approvalStatus === 'approved' ? 'Approve food?' : 'Reset approval?',
      text: approvalStatus === 'approved'
        ? 'This food will be allowed to appear on the customer menu.'
        : 'This food will be set back to pending approval.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#16a34a',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Confirm',
      cancelButtonText: 'Cancel',
    });

    if (!result.isConfirmed) return;
  }

  router.patch(`/admin/foods/${food.id}/approval`, {
    approval_status: approvalStatus,
    rejection_reason: rejectionReason || undefined,
  }, { preserveScroll: true });
}

async function deleteFood(id) {
  const result = await window.Swal.fire({
    title: 'Delete food item?',
    text: 'This food item will be permanently removed.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
  });

  if (!result.isConfirmed) return;

  router.delete(`/admin/foods/${id}`, { preserveScroll: true });
}

function reloadFoods() {
  router.reload({
    only: ['foods', 'flash'],
    preserveScroll: true,
    preserveState: true,
  });
}

function handleLiveRefresh() {
  reloadFoods();
}

onMounted(() => {
  window.addEventListener('app:live-refresh', handleLiveRefresh);
  window.addEventListener('focus', handleLiveRefresh);
  pollTimer = window.setInterval(() => {
    if (!document.hidden) {
      reloadFoods();
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
  <Head title="Admin Foods" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-2xl font-bold text-green-600 tracking-tight">Admin Foods</h1>
    </section>

    <!-- <section v-if="flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
      {{ flash.success }}
    </section> -->
    <section v-if="flash?.error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
      {{ flash.error }}
    </section>

    <section class="bg-white rounded-2xl shadow border p-4 mb-6">
      <form @submit.prevent="search" class="flex flex-col sm:flex-row gap-3 items-center">
        <input v-model="form.q" type="text" placeholder="Search food..." class="border border-gray-300 rounded-xl px-4 py-2 text-sm w-full sm:w-64">
        <select v-model="form.availability" class="border border-gray-300 rounded-xl px-4 py-2 text-sm w-full sm:w-auto">
          <option value="">All</option>
          <option value="available">Available</option>
          <option value="unavailable">Unavailable</option>
        </select>
        <button type="submit" class="px-5 py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700">Search</button>
        <button type="button" @click="clearFilters" class="px-5 py-2 text-sm border border-gray-300 rounded-xl bg-white text-gray-600 hover:bg-green-50">
          Clear
        </button>
      </form>
    </section>

    <section class="bg-white rounded-2xl shadow border p-4">
      <div class="flex justify-between items-center mb-5">
        <p class="text-sm text-gray-600">
          Total foods: <span class="font-semibold">{{ foods.total }}</span>
        </p>
        <Link href="/admin/foods/create" class="px-5 py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700">
          + Add Food
        </Link>
      </div>

      <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 bg-gray-50 text-gray-600">
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Image</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Category</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Price</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Stock</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Approval</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!foods.data.length">
              <td colspan="8" class="py-12 text-center">
                <div class="text-5xl mb-3">📂</div>
                <p class="text-gray-500 text-base">No foods found.</p>
              </td>
            </tr>

            <tr v-for="food in foods.data" :key="food.id" class="border-b border-gray-100 hover:bg-green-50/40">
              <td class="px-4 py-3">
                <img v-if="food.image_url" :src="food.image_url" class="w-14 h-14 object-cover rounded-xl ring-1 ring-gray-200 shadow-sm">
                <div v-else class="w-14 h-14 bg-gray-100 rounded-xl ring-1 ring-gray-200"></div>
              </td>
              <td class="px-4 py-3"><p class="font-semibold text-gray-800">{{ food.name }}</p></td>
              <td class="px-4 py-3"><span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ food.category_name }}</span></td>
              <td class="px-4 py-3"><span class="font-semibold text-gray-900">₱{{ Number(food.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span></td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="food.available_quantity === null ? 'bg-blue-50 text-blue-700' : Number(food.available_quantity) > 0 ? 'bg-gray-100 text-gray-700' : 'bg-red-50 text-red-700'">
                  {{ food.available_quantity === null ? 'Unlimited' : food.available_quantity }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="space-y-2">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="food.approval_status === 'approved'
                      ? 'bg-green-50 text-green-700'
                      : food.approval_status === 'rejected'
                        ? 'bg-red-50 text-red-700'
                        : 'bg-amber-50 text-amber-700'"
                  >
                    {{ food.approval_status }}
                  </span>
                  <p v-if="food.approved_by_name" class="text-[11px] text-gray-500">By {{ food.approved_by_name }}</p>
                  <p v-if="food.rejection_reason" class="max-w-[180px] text-[11px] text-red-600">{{ food.rejection_reason }}</p>
                </div>
              </td>
              <td class="px-4 py-3">
                <button
                  type="button"
                  @click="toggleAvailability(food.id, food.is_available)"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition duration-300"
                  :class="food.is_available ? 'bg-green-600' : 'bg-gray-300'"
                  :disabled="food.approval_status !== 'approved'"
                >
                  <span class="inline-block h-5 w-5 transform rounded-full bg-white transition duration-300" :class="food.is_available ? 'translate-x-5' : 'translate-x-1'"></span>
                </button>
                <span class="ml-2 text-xs font-medium" :class="food.is_available ? 'text-green-700' : 'text-gray-500'">
                  {{ food.is_available ? 'Available' : 'Unavailable' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2 flex-wrap">
                  <button
                    v-if="food.approval_status !== 'approved'"
                    type="button"
                    @click="updateApproval(food, 'approved')"
                    class="px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700"
                  >
                    Approve
                  </button>
                  <button
                    v-if="food.approval_status !== 'rejected'"
                    type="button"
                    @click="updateApproval(food, 'rejected')"
                    class="px-3 py-1.5 text-xs font-medium bg-orange-500 text-white rounded-lg hover:bg-orange-600"
                  >
                    Reject
                  </button>
                  <button
                    v-if="food.approval_status !== 'pending'"
                    type="button"
                    @click="updateApproval(food, 'pending')"
                    class="px-3 py-1.5 text-xs font-medium bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                  >
                    Pending
                  </button>
                  <Link :href="`/admin/foods/${food.id}/edit`" class="px-3 py-1.5 text-xs font-medium bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Edit</Link>
                  <button type="button" @click="deleteFood(food.id)" class="px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="foods?.links?.length" class="mt-8">
        <div class="flex flex-wrap items-center gap-2">
          <template v-for="(link, idx) in foods.links" :key="idx">
            <span v-if="!link.url" class="px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-200 text-gray-400" v-html="link.label"></span>
            <Link
              v-else
              :href="link.url"
              class="px-3 py-2 text-xs sm:text-sm rounded-lg border transition"
              :class="link.active ? 'bg-green-600 border-green-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
