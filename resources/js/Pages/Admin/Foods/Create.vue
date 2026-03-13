<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});

const form = useForm({
  category_id: '',
  name: '',
  price: '',
  available_quantity: '',
  image: null,
  is_available: true,
});

function onFileChange(event) {
  form.image = event.target.files?.[0] || null;
}

function submit() {
  form.post('/admin/foods', {
    forceFormData: true,
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Admin Add Food" />

  <AppLayout>
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow border p-8">
      <h1 class="text-xl font-bold text-green-600 mb-6">Add Food</h1>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
          <select v-model="form.category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm">
            <option value="">Select Category</option>
            <option v-for="category in categories" :key="category.id" :value="String(category.id)">
              {{ category.name }}
            </option>
          </select>
          <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Food Name</label>
          <input v-model="form.name" type="text" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm">
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
          <input v-model="form.price" type="number" step="0.01" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm">
          <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Available Quantity</label>
          <input v-model="form.available_quantity" type="number" min="0" placeholder="Leave blank for no limit" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm">
          <p class="mt-1 text-xs text-gray-500">Set stock quantity. Leave blank to allow unlimited orders.</p>
          <p v-if="form.errors.available_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.available_quantity }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
          <input type="file" @change="onFileChange" class="w-full text-sm border border-gray-300 rounded-xl px-4 py-2.5">
          <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
        </div>

        <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
          <input id="is_available" v-model="form.is_available" type="checkbox" class="w-5 h-5 text-green-600 rounded">
          <label for="is_available" class="text-sm font-medium text-gray-700">Available</label>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <Link href="/admin/foods" class="px-5 py-2 text-sm border border-gray-300 rounded-xl bg-white text-gray-600 hover:bg-green-50">
            Back
          </Link>
          <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 disabled:opacity-60">
            Save
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
