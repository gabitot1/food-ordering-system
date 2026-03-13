<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 px-4">
    <div>
      <a href="/" class="inline-flex items-center justify-center">
        <img
          :src="'/images/logo-blue.png'"
          alt="Logo"
          class="h-20 w-20 object-contain"
        />
      </a>
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
      <slot />
    </div>
  </div>
</template>
