<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

const props = defineProps({
  category: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  name: props.category.name || '',
});

function submit() {
  form.patch(`/admin/categories/${props.category.id}`, {
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Admin Edit Category" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-2xl font-bold text-green-600 tracking-tight">Edit Category</h1>
    </section>

    <div class="w-full max-w-xl mx-auto bg-white shadow rounded-2xl border p-8">
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700">Update Category Details</h2>
        <p class="text-sm text-gray-500 mt-1">Modify the category name below.</p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-2">Category Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 rounded-xl border border-gray-300"
          >
          <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <div class="flex justify-between items-center pt-2">
          <Link href="/admin/categories" class="text-sm text-gray-500 hover:text-gray-700">← Cancel</Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 rounded-xl text-white font-semibold bg-green-600 hover:bg-green-700 disabled:opacity-60"
          >
            Update Category
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
