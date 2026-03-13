<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const props = defineProps({
  token: {
    type: String,
    required: true,
  },
  email: {
    type: String,
    default: '',
  },
});

const form = useForm({
  token: props.token,
  email: props.email || '',
  password: '',
  password_confirmation: '',
});

function submit() {
  form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
}
</script>

<template>
  <Head title="Reset Password" />

  <GuestLayout>
    <h1 class="text-xl font-bold text-gray-900 mb-2">Reset Password</h1>
    <p class="text-sm text-gray-500 mb-6">Enter your new password.</p>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input v-model="form.email" type="email" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input v-model="form.password" type="password" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input v-model="form.password_confirmation" type="password" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
      </div>

      <button type="submit" :disabled="form.processing" class="w-full bg-green-600 text-white py-2.5 rounded-xl hover:bg-green-700 disabled:opacity-60">
        Reset Password
      </button>
    </form>
  </GuestLayout>
</template>
