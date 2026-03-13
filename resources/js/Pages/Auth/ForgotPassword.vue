<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

defineProps({
  status: {
    type: String,
    default: '',
  },
});

const form = useForm({
  email: '',
});

function submit() {
  form.post('/forgot-password');
}
</script>

<template>
  <Head title="Forgot Password" />

  <GuestLayout>
    <h1 class="text-xl font-bold text-gray-900 mb-2">Forgot Password</h1>
    <p class="text-sm text-gray-500 mb-6">
      Enter your email and we will send a reset link.
    </p>

    <p v-if="status" class="mb-4 p-2 rounded bg-green-50 text-green-700 text-sm">{{ status }}</p>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input v-model="form.email" type="email" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
      </div>

      <button type="submit" :disabled="form.processing" class="w-full bg-green-600 text-white py-2.5 rounded-xl hover:bg-green-700 disabled:opacity-60">
        Email Password Reset Link
      </button>
    </form>

    <p class="mt-4 text-sm">
      <Link href="/login" class="text-green-700 hover:underline">Back to login</Link>
    </p>
  </GuestLayout>
</template>
