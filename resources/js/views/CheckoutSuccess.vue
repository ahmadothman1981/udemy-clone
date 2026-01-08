<template>
    <div>
        <Navbar />
        <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 py-16">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Success Animation -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 shadow-xl mb-6 animate-bounce-slow">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-3">Order Complete!</h1>
                    <p class="text-lg text-gray-600">Thank you for your purchase. You're ready to start learning!</p>
                </div>

                <!-- Order Details Card -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-8">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-white">Order Confirmed</h2>
                                <p class="text-green-100 text-sm">{{ orderNumber }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-white">£{{ orderTotal.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Purchased Courses -->
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Your New Courses</h3>
                        <div class="space-y-4">
                            <div v-for="course in purchasedCourses" :key="course.id" 
                                 class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer"
                                 @click="startLearning(course)">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ course.title }}</h4>
                                    <p class="text-sm text-green-600 font-medium">Ready to watch</p>
                                </div>
                                <button class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                    </svg>
                                    Start
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <router-link to="/my-courses" 
                                 class="flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Go to My Learning
                    </router-link>
                    <router-link to="/" 
                                 class="flex items-center justify-center gap-2 px-8 py-4 bg-white text-gray-700 font-bold rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Continue Shopping
                    </router-link>
                </div>

                <!-- Email Confirmation Notice -->
                <div class="mt-8 text-center text-sm text-gray-500">
                    <p>A confirmation email has been sent to your registered email address.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useCheckoutStore } from '../stores/checkout';
import Navbar from '../components/Navbar.vue';

const router = useRouter();
const checkoutStore = useCheckoutStore();

const orderNumber = computed(() => {
    return checkoutStore.orderResult?.order?.order_number || 'ORD-XXXXXXXXXX';
});

const orderTotal = computed(() => {
    return checkoutStore.orderResult?.order?.total || 0;
});

const purchasedCourses = computed(() => {
    return checkoutStore.orderResult?.order?.courses || [];
});

const startLearning = (course) => {
    router.push(`/learn/course/${course.id}`);
};
</script>

<style scoped>
@keyframes bounce-slow {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}
</style>
