<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  status: {
    type: String,
    default: '',
  },
});

const profileForm = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  profile_photo: null,
  remove_profile_photo: 0,
});

const currentPhotoUrl = computed(() => {
  if (profileForm.remove_profile_photo) return null;
  if (profileForm.profile_photo instanceof File) {
    return URL.createObjectURL(profileForm.profile_photo);
  }
  return props.user.profile_photo_url || null;
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const deleteForm = useForm({
  password: '',
});
const activeTab = ref('profile');

function updateProfile() {
  profileForm
    .transform((data) => ({
      name: data.name,
      email: data.email,
      profile_photo: data.profile_photo,
      remove_profile_photo: data.remove_profile_photo,
      _method: 'patch',
    }))
    .post('/profile', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      profileForm.profile_photo = null;
      profileForm.remove_profile_photo = 0;
    },
  });
}

function onProfilePhotoChange(event) {
  const [file] = event.target.files || [];
  profileForm.profile_photo = file || null;
  if (file) {
    profileForm.remove_profile_photo = 0;
  }
}

function removeProfilePhoto() {
  profileForm.profile_photo = null;
  profileForm.remove_profile_photo = 1;
}

function updatePassword() {
  passwordForm.put('/password', {
    preserveScroll: true,
    errorBag: 'updatePassword',
    onSuccess: () => passwordForm.reset(),
  });
}

function deleteAccount() {
  if (!confirm('Delete account? This action is permanent.')) return;
  deleteForm.delete('/profile', {
    preserveScroll: true,
    errorBag: 'userDeletion',
  });
}
</script>

<template>
  <Head title="Profile" />

  <AppLayout>
    <section class="mb-5">
      <h1 class="text-xl font-semibold text-gray-800">Profile</h1>
    </section>

    <!-- <section v-if="status === 'profile-updated'" class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
      Profile updated.
    </section>
    <section v-if="status === 'password-updated'" class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
      Password updated.
    </section> -->

    <div class="mx-auto max-w-4xl space-y-6">
      <div class="inline-flex overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <button
          type="button"
          @click="activeTab = 'profile'"
          class="px-4 py-2 text-sm font-medium transition"
          :class="activeTab === 'profile' ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
        >
          Profile Information
        </button>
        <button
          type="button"
          @click="activeTab = 'security'"
          class="border-l border-gray-200 px-4 py-2 text-sm font-medium transition"
          :class="activeTab === 'security' ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
        >
          Security
        </button>
      </div>

      <div v-if="activeTab === 'profile'" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6 flex items-center gap-2 text-sm font-semibold text-gray-900">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21a8 8 0 0 0-16 0"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Profile Information</span>
        </div>

        <form @submit.prevent="updateProfile" class="space-y-6">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="shrink-0 rounded-full border border-green-100 bg-green-50 p-1" style="width: 72px; height: 72px;">
              <div class="relative overflow-hidden rounded-full bg-green-600" style="width: 64px; height: 64px;">
                <img v-if="currentPhotoUrl" :src="currentPhotoUrl" alt="Profile photo" class="absolute inset-0 block h-full w-full object-cover">
                <div v-else class="flex h-full w-full items-center justify-center text-lg font-semibold text-white">
                  {{ (props.user.name || 'A').charAt(0).toUpperCase() }}
                </div>
              </div>
            </div>

            <div>
              <div class="inline-flex overflow-hidden rounded-xl border border-gray-200 bg-[#f6f6f6]">
                <label class="cursor-pointer px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                  <span>Change Avatar</span>
                  <input type="file" accept="image/*" class="hidden" @change="onProfilePhotoChange">
                </label>
                <button
                  v-if="currentPhotoUrl"
                  type="button"
                  @click="removeProfilePhoto"
                  class="border-l border-gray-200 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                >
                  Remove
                </button>
              </div>
              <p class="mt-2 text-xs text-gray-500">{{ profileForm.profile_photo?.name || 'PNG or JPG up to 2MB' }}</p>
              <p v-if="profileForm.errors.profile_photo" class="mt-1 text-sm text-red-600">{{ profileForm.errors.profile_photo }}</p>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Name</label>
            <input v-model="profileForm.name" type="text" class="w-full rounded-xl border border-gray-200 px-3 py-3 text-sm text-gray-800" required>
            <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600">{{ profileForm.errors.name }}</p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Email</label>
            <input v-model="profileForm.email" type="email" class="w-full rounded-xl border border-gray-200 px-3 py-3 text-sm text-gray-800" required>
            <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600">{{ profileForm.errors.email }}</p>
          </div>

          <div class="flex justify-end">
            <button type="submit" :disabled="profileForm.processing" class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-green-700 disabled:opacity-60">
              Save Changes
            </button>
          </div>
        </form>
      </div>

      <div v-if="activeTab === 'security'" class="space-y-6">
        <div class="bg-white rounded-2xl shadow border p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Update Password</h2>
          <form @submit.prevent="updatePassword" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
              <input v-model="passwordForm.current_password" type="password" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
              <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <input v-model="passwordForm.password" type="password" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
              <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
              <input v-model="passwordForm.password_confirmation" type="password" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
              <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.password_confirmation }}</p>
            </div>
            <button type="submit" :disabled="passwordForm.processing" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 disabled:opacity-60">
              Update Password
            </button>
          </form>
        </div>

        <div class="bg-white rounded-2xl shadow border p-6">
          <h2 class="text-lg font-semibold text-red-700 mb-4">Delete Account</h2>
          <p class="text-sm text-gray-600 mb-4">Once your account is deleted, all resources and data will be permanently deleted.</p>
          <form @submit.prevent="deleteAccount" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input v-model="deleteForm.password" type="password" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
              <p v-if="deleteForm.errors.password" class="mt-1 text-sm text-red-600">{{ deleteForm.errors.password }}</p>
            </div>
            <button type="submit" :disabled="deleteForm.processing" class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:opacity-60">
              Delete Account
            </button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
