<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  foods: {
    type: Array,
    default: () => [],
  },
  selectedCategory: {
    type: Number,
    default: null,
  },
});

function setCategory(categoryId) {
  router.get('/menu', { category: categoryId || undefined }, { preserveState: true, replace: true });
}

const detailsOpen = ref(false);
const selectedFood = ref(null);
const detailQty = ref(1);
const detailInstruction = ref('');
const addingFoodId = ref(null);
const showCartToast = ref(false);
const cartToastProgress = ref(100);
let cartToastTimer = null;
let cartToastHideTimer = null;

function addToCart(foodId, quantity = 1, instruction = '') {
  if (addingFoodId.value !== null) return;

  addingFoodId.value = foodId;

  window.axios
    .post(
      '/cart/add',
      {
        food_id: foodId,
        quantity,
        instruction: instruction || undefined,
      },
      {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    )
    .then(() => {
      closeDetails();
      showCartToast.value = true;
      startCartToastProgress();
    })
    .catch((error) => {
      if (window.Swal) {
        window.Swal.fire({
          icon: 'error',
          title: 'Request failed',
          text: error?.response?.data?.message || 'Something went wrong.',
          confirmButtonColor: '#16a34a',
        });
      }
    })
    .finally(() => {
      addingFoodId.value = null;
    });
}

function openDetails(food) {
  if (!food?.is_orderable) return;
  selectedFood.value = food;
  detailQty.value = 1;
  detailInstruction.value = '';
  detailsOpen.value = true;
}

function closeDetails() {
  detailsOpen.value = false;
}

function closeCartToast() {
  showCartToast.value = false;
  clearCartToastTimers();
}

function clearCartToastTimers() {
  if (cartToastTimer) {
    window.clearInterval(cartToastTimer);
    cartToastTimer = null;
  }

  if (cartToastHideTimer) {
    window.clearTimeout(cartToastHideTimer);
    cartToastHideTimer = null;
  }
}

function startCartToastProgress() {
  clearCartToastTimers();
  cartToastProgress.value = 100;

  const startedAt = Date.now();
  const duration = 2000;

  cartToastTimer = window.setInterval(() => {
    const elapsed = Date.now() - startedAt;
    const nextValue = Math.max(0, 100 - (elapsed / duration) * 100);
    cartToastProgress.value = nextValue;

    if (nextValue <= 0) {
      if (cartToastTimer) {
        window.clearInterval(cartToastTimer);
        cartToastTimer = null;
      }
    }
  }, 30);

  cartToastHideTimer = window.setTimeout(() => {
    showCartToast.value = false;
    clearCartToastTimers();
  }, duration);
}

function changeQty(step) {
  if (!selectedFood.value) return;

  const nextQty = Math.max(1, Number(detailQty.value) + step);
  const maxQty = selectedFood.value.available_quantity;
  detailQty.value = maxQty === null ? nextQty : Math.min(nextQty, Math.max(1, Number(maxQty)));
}

function submitDetailsAdd() {
  if (!selectedFood.value) return;
  addToCart(selectedFood.value.id, detailQty.value, detailInstruction.value);
}

function money(value) {
  return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function isAdding(foodId) {
  return addingFoodId.value === foodId;
}

const emptyState = computed(() => props.foods.length === 0);
</script>

<template>
  <Head title="Menu" />

  <AppLayout>
    <section id="menu" class="mb-5 scroll-mt-24">
      <h1 class="text-2xl sm:text-3xl font-bold text-green-700 tracking-tight">Menu</h1>
    </section>
      <!-- <section v-if="flash?.error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
      {{ flash.error }}
    </section>
    <section
      class="mb-4 p-3 rounded-xl text-sm border"
      :class="storeStatus?.is_open_now ? 'bg-green-50 border-green-200 text-green-800' : 'bg-amber-50 border-amber-200 text-amber-800'"
    >
      {{ storeStatus?.message || (storeStatus?.is_open_now ? 'Store is open now.' : 'Store is currently closed.') }}
    </section> -->

    <section class="flex gap-3 mb-8 overflow-x-auto pb-2">
      <button
        type="button"
        @click="setCategory(null)"
        class="px-5 py-2 rounded-full text-sm font-medium transition"
        :class="selectedCategory ? 'bg-gray-200 text-gray-700' : 'bg-green-600 text-white shadow-lg'"
      >
        All
      </button>

      <button
        v-for="category in categories"
        :key="category.id"
        type="button"
        @click="setCategory(category.id)"
        class="px-5 py-2 rounded-full text-sm font-medium transition whitespace-nowrap"
        :class="selectedCategory === category.id ? 'bg-green-600 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-green-600 hover:text-white'"
      >
        {{ category.name }}
      </button>
    </section>

    <section v-if="emptyState" class="text-center py-20 text-gray-400">
      <p class="text-lg">No foods available in this category.</p>
    </section>

    <section v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">
      <article
        v-for="food in foods"
        :key="food.id"
        class="group bg-white rounded-3xl shadow-md hover:shadow-2xl transition duration-500 overflow-hidden"
      >
        <div class="relative overflow-hidden">
          <img v-if="food.image_url" :src="food.image_url" class="w-full h-52 object-cover group-hover:scale-105 transition duration-500">
          <div v-else class="w-full h-52 bg-gray-100"></div>
          <span
            class="absolute top-4 left-4 text-white text-xs px-3 py-1 rounded-full shadow"
            :class="food.is_orderable ? 'bg-green-600' : 'bg-gray-500'"
          >
            {{ food.is_orderable ? 'Available' : 'Unavailable' }}
          </span>
          <span v-if="food.available_quantity !== null" class="absolute top-4 right-4 bg-white/90 text-gray-700 text-xs px-3 py-1 rounded-full shadow">
            Stock: {{ food.available_quantity }}
          </span>
        </div>

        <div class="p-4 sm:p-6">
          <h3 class="font-semibold text-lg text-gray-800 group-hover:text-green-600 transition">{{ food.name }}</h3>
          <!-- <p class="text-sm text-gray-500 mt-2 min-h-[40px]">{{ food.description || 'No description available.' }}</p> -->
          <!-- <p v-if="food.available_quantity !== null" class="mt-2 text-xs text-gray-500">Available quantity: {{ food.available_quantity }}</p> -->

          <button
            type="button"
            @click="openDetails(food)"
            class="mt-3 w-full bg-gray-100 text-gray-700 py-2 rounded-xl hover:bg-gray-200 transition text-sm"
          >
            Details
          </button>

          <div class="flex justify-between items-center mt-5">
            <span class="text-green-700 font-bold text-xl">₱{{ money(food.price) }}</span>

            <button
              type="button"
              @click="addToCart(food.id, 1)"
              :disabled="!food.is_orderable || addingFoodId !== null"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm sm:text-base transition shadow-md disabled:cursor-not-allowed"
              :class="food.is_orderable && !isAdding(food.id)
                ? 'bg-green-600 text-white hover:bg-green-700'
                : 'bg-gray-200 text-gray-500 shadow-none'"
            >
              <span
                v-if="isAdding(food.id)"
                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
              ></span>
              {{ !food.is_orderable ? 'Add' : isAdding(food.id) ? 'Adding...' : 'Add' }}
            </button>
          </div>
        </div>
      </article>
    </section>

    <!-- <section class="mt-8">
      <Link href="/cart" class="inline-flex items-center px-4 py-2 rounded-xl bg-gray-900 text-white text-sm hover:bg-black transition">
        Go to Cart
      </Link>
    </section> -->

    <div
      v-if="detailsOpen && selectedFood"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-[10000]"
      @click.self="closeDetails"
    >
      <div class="relative bg-white w-full mx-4 max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl p-5 sm:p-6">
        <button @click="closeDetails" class="absolute top-4 right-4 text-gray-500 hover:text-black text-xl">✕</button>

        <img
          v-if="selectedFood.image_url"
          :src="selectedFood.image_url"
          :alt="selectedFood.name"
          class="w-full h-60 object-cover rounded-xl mb-4"
        >
        <div v-else class="w-full h-60 rounded-xl mb-4 bg-gray-100"></div>

        <h3 class="text-xl font-bold mb-2">{{ selectedFood.name }}</h3>
        <p class="text-sm text-gray-500 mb-4">{{ selectedFood.description || 'No description available.' }}</p>
        <p v-if="selectedFood.available_quantity !== null" class="text-sm text-gray-500 mb-4">Available quantity: {{ selectedFood.available_quantity }}</p>

        <div class="flex justify-between items-center mb-4">
          <span class="font-semibold">Quantity</span>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="changeQty(-1)"
              class="h-8 w-8 rounded-lg bg-gray-200 text-sm font-semibold text-gray-700 transition hover:bg-gray-300"
            >
              -
            </button>
            <span class="min-w-8 text-center text-sm font-medium text-gray-700">{{ detailQty }}</span>
            <button
              type="button"
              @click="changeQty(1)"
              :disabled="selectedFood.available_quantity !== null && detailQty >= selectedFood.available_quantity"
              class="h-8 w-8 rounded-lg bg-gray-200 text-sm font-semibold text-gray-700 transition hover:bg-gray-300 disabled:opacity-50"
            >
              +
            </button>
          </div>
        </div>

        <textarea
          v-model="detailInstruction"
          placeholder="Take note / Special instruction (optional)"
          class="w-full border rounded-lg p-2 text-sm mb-3"
        ></textarea>

        <button
          type="button"
          @click="submitDetailsAdd"
          :disabled="addingFoodId !== null || !selectedFood.is_orderable"
          class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 py-2 text-white transition hover:bg-green-800 disabled:opacity-60"
        >
          <span
            v-if="addingFoodId === selectedFood.id"
            class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
          ></span>
          {{ !selectedFood.is_orderable ? 'Unavailable' : addingFoodId === selectedFood.id ? 'Adding to Cart...' : 'Add to Cart' }}
        </button>
      </div>
    </div>

    <a href="#menu" class="fixed bottom-6 right-6 bg-green-600 text-white p-4 rounded-full shadow-lg hover:bg-green-700 transition z-40">
      ↑
    </a>

    <div
      id="cartToast"
      class="fixed right-4 top-24 z-[11000] flex w-[calc(100%-2rem)] justify-end transition-all duration-300 sm:w-auto"
      :class="showCartToast ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
    >
      <div
        class="relative w-full max-w-md rounded-2xl bg-white px-6 py-5 shadow-2xl transition-all duration-300 sm:py-6"
        :class="showCartToast ? 'translate-x-0 opacity-100' : 'translate-x-4 opacity-0'"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="text-3xl">🛒</div>
            <div>
              <p class="text-lg font-semibold text-gray-800">Added to Cart</p>
              <p class="text-sm text-gray-500">Your item was added successfully</p>
            </div>
          </div>
          <button
            type="button"
            @click="closeCartToast"
            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xl font-semibold leading-none text-gray-500 transition hover:bg-gray-100 hover:text-gray-800"
            aria-label="Close added to cart notification"
          >
            ×
          </button>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-2 overflow-hidden rounded-b-2xl bg-green-100">
          <div
            class="h-full bg-green-600 transition-[width] duration-75 ease-linear"
            :style="{ width: `${cartToastProgress}%` }"
          ></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
