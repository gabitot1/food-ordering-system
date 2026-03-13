<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

function submit() {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
}
</script>

<template>
  <Head title="Register" />

  <GuestLayout>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Create Account</h1>
    <p class="text-sm text-gray-500 mb-6">Sign up to start ordering.</p>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input v-model="form.name" type="text" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required autofocus>
        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input v-model="form.password" type="password" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input v-model="form.password_confirmation" type="password" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
      </div>

      <button type="submit" :disabled="form.processing" class="w-full bg-green-600 text-white py-2.5 rounded-xl hover:bg-green-700 disabled:opacity-60">
        Register
      </button>
    </form>

    <p class="mt-4 text-sm text-gray-600">
      Already registered?
      <Link href="/login" class="text-green-700 hover:underline">Login</Link>
    </p>
  </GuestLayout>
</template>
