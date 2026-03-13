<script setup>
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  // flash: {
  //   type: Object,
  //   default: () => ({ success: null, error: null }),
  // },
});

const form = useForm({
  name: '',
});

function createCategory() {
  form.post('/admin/categories', {
    preserveScroll: true,
    onSuccess: () => form.reset('name'),
  });
}

async function deleteCategory(id) {
  const result = await window.Swal.fire({
    title: 'Delete category?',
    text: 'This category will be removed.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
  });

  if (!result.isConfirmed) return;

  router.delete(`/admin/categories/${id}`, { preserveScroll: true });
}
</script>

<template>
  <Head title="Admin Categories" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-2xl font-bold text-green-600 tracking-tight">Admin Categories</h1>
    </section>

    <section v-if="flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
      {{ flash.success }}
    </section>
    <section v-if="flash?.error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
      {{ flash.error }}
    </section>

    <section class="bg-white rounded-2xl p-6 border shadow-sm mb-6">
      <h2 class="text-md font-semibold text-gray-700 mb-4">Add New Category</h2>
      <form @submit.prevent="createCategory" class="flex flex-col sm:flex-row gap-4">
        <input
          v-model="form.name"
          type="text"
          placeholder="Enter category name..."
          class="flex-1 px-3 py-2 sm:px-4 sm:py-3 rounded-xl border border-gray-300"
          required
        >
        <button
          type="submit"
          :disabled="form.processing"
          class="px-4 py-2 sm:px-6 sm:py-3 rounded-xl text-white font-semibold bg-green-600 hover:bg-green-700 disabled:opacity-60"
        >
          + Add
        </button>
      </form>
      <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
    </section>

    <section class="bg-white rounded-2xl border shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-700">Category List</h2>
      </div>

      <div v-if="!categories.length" class="py-12 text-center">
        <div class="text-5xl mb-3">📂</div>
        <p class="text-gray-500 text-base">No categories yet.</p>
        <p class="text-xs text-gray-400 mt-1">Add your first category above.</p>
      </div>

      <article
        v-for="category in categories"
        :key="category.id"
        class="flex items-center justify-between px-6 py-4 border-b hover:bg-green-50/60 transition"
      >
        <div>
          <p class="font-semibold text-gray-800">{{ category.name }}</p>
          <p class="text-sm text-gray-500 mt-1">{{ category.foods_count }} items</p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="`/admin/categories/${category.id}/edit`"
            class="px-3 py-2 text-sm font-medium bg-yellow-500 text-white rounded-xl hover:bg-yellow-600"
          >
            Edit
          </Link>
          <button
            type="button"
            @click="deleteCategory(category.id)"
            class="px-3 py-2 text-sm font-medium bg-red-600 text-white rounded-xl hover:bg-red-700"
          >
            Delete
          </button>
        </div>
      </article>
    </section>
  </AppLayout>
</template>
