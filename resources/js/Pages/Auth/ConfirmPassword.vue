<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const form = useForm({
  password: '',
});

function submit() {
  form.post('/confirm-password', {
    onFinish: () => form.reset('password'),
  });
}
</script>

<template>
  <Head title="Confirm Password" />

  <GuestLayout>
    <h1 class="text-xl font-bold text-gray-900 mb-2">Confirm Password</h1>
    <p class="text-sm text-gray-500 mb-6">This is a secure area. Confirm your password to continue.</p>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input v-model="form.password" type="password" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
      </div>

      <button type="submit" :disabled="form.processing" class="w-full bg-green-600 text-white py-2.5 rounded-xl hover:bg-green-700 disabled:opacity-60">
        Confirm
      </button>
    </form>
  </GuestLayout>
</template>
