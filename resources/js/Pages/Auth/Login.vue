<script setup>
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
  status: {
    type: String,
    default: '',
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
}
</script>

<template>
  <Head title="Login" />

  <div class="flex min-h-screen items-center justify-center bg-[linear-gradient(180deg,#086972_0%,#086972_45%,#17b978_100%)] px-4 py-10">
    <div class="mx-auto flex w-full max-w-5xl justify-center">
      <div class="w-full max-w-4xl overflow-hidden rounded-[28px] bg-white shadow-2xl">
        <div class="grid min-h-[560px] lg:grid-cols-2">
          <div class="relative hidden lg:block">
            <img
              :src="'/images/system_img.jpg'"
              alt="Ordering system"
              class="h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(8,105,114,0.10),rgba(8,105,114,0.28))]"></div>
          </div>

          <div class="flex items-center justify-center p-8 sm:p-12 lg:p-16">
            <div class="w-full max-w-md">
              <div class="text-center">
                <h1 class="text-xl font-semibold text-gray-500">Welcome!</h1>
                <p class="mt-2 text-2xl font-bold uppercase tracking-wide text-emerald-600">PSG Ordering System</p>
              </div>

              <p v-if="status" class="mt-6 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700">{{ status }}</p>

              <form @submit.prevent="submit" class="mt-8 space-y-5">
                <div>
                  <input
                    v-model="form.email"
                    type="email"
                    placeholder="Email"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-center text-sm text-gray-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    required
                    autofocus
                  >
                  <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                  <input
                    v-model="form.password"
                    type="password"
                    placeholder="Password"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-center text-sm text-gray-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    required
                  >
                  <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
                </div>

                <label class="flex items-center justify-center gap-2 text-sm text-gray-500">
                  <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                  Remember me
                </label>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-60"
                >
                  {{ form.processing ? 'Logging in...' : 'Login' }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
