<script setup>
import AppLayout from '../../Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    foods: Array,
    auth: Object
})

function addToCart(foodId) {
    router.post('/cart/add', {
        food_id: foodId,
        quantity: 1
    })
}

function toggleAvailability(foodId) {
    router.patch(`/admin/foods/${foodId}/toggle`)
}
</script>

<template>

<AppLayout title="Foods">

<div class="min-h-screen py-4 sm:py-10" style="background-color:#3a5a40;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <h2 class="text-lg sm:text-2xl font-semibold text-white mb-6">
            Foods
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-8">

            <div
                v-for="food in foods"
                :key="food.id"
                class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 flex flex-col"
            >

                <img
                    v-if="food.image"
                    :src="`/storage/${food.image}`"
                    class="h-48 w-full object-cover hover:scale-105 transition duration-500"
                />

                <div v-else class="h-48 bg-gray-200"></div>

                <div class="p-3 sm:p-5 flex flex-col justify-between flex-1">

                    <div>
                        <h3 class="text-sm sm:text-lg font-semibold text-gray-800">
                            {{ food.name }}
                        </h3>

                        <p class="text-xs sm:text-sm text-gray-500 mt-1">
                            ₱{{ food.price }}
                        </p>

                        <span
                            v-if="!food.is_available"
                            class="inline-block mt-2 text-xs bg-red-100 text-red-600 px-2 py-1 rounded-md"
                        >
                            Not Available
                        </span>
                    </div>

                    <div class="mt-5">

                        <button
                            v-if="auth?.user?.is_admin"
                            @click="toggleAvailability(food.id)"
                            class="w-full py-2 text-sm text-white rounded-lg mb-3"
                            :class="food.is_available ? 'bg-red-600' : 'bg-green-600'"
                        >
                            {{ food.is_available ? 'Set Unavailable' : 'Set Available' }}
                        </button>

                        <button
                            v-if="food.is_available"
                            @click="addToCart(food.id)"
                            class="w-full py-2 text-sm text-white bg-[#588157] rounded-lg hover:bg-[#344e41]"
                        >
                            Add to Cart
                        </button>

                        <button
                            v-else
                            class="w-full py-2 text-sm bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed"
                        >
                            Unavailable
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

</AppLayout>

</template>
