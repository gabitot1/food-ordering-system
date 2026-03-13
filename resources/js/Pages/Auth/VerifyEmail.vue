<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

defineProps({
  status: {
    type: String,
    default: '',
  },
});

const resendForm = useForm({});
const logoutForm = useForm({});

function resendVerification() {
  resendForm.post('/email/verification-notification');
}

function logout() {
  logoutForm.post('/logout');
}
</script>

<template>
  <Head title="Verify Email" />

  <GuestLayout>
    <div class="mb-4 text-sm text-gray-600">
      Thanks for signing up. Verify your email address using the link we sent. If you did not receive it, request a new one below.
    </div>

    <div
      v-if="status === 'verification-link-sent'"
      class="mb-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700"
    >
      A new verification link has been sent to your email address.
    </div>

    <div class="mt-4 flex items-center justify-between gap-3">
      <button
        type="button"
        @click="resendVerification"
        :disabled="resendForm.processing"
        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-60"
      >
        Resend Verification Email
      </button>

      <button
        type="button"
        @click="logout"
        :disabled="logoutForm.processing"
        class="text-sm text-gray-600 underline hover:text-gray-900 disabled:opacity-60"
      >
        Log Out
      </button>
    </div>

    <div class="mt-4 text-center text-sm text-gray-500">
      <Link href="/login" class="hover:text-gray-700">Back to login</Link>
    </div>
  </GuestLayout>
</template>
