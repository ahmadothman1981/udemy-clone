<template>
    <div>
        <Navbar />
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
                    <p class="mt-2 text-gray-600">Complete your purchase to start learning</p>
                </div>

                <!-- Loading State -->
                <div v-if="checkoutStore.loading" class="flex justify-center py-20">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                </div>

                <!-- Empty Cart -->
                <div v-else-if="checkoutStore.items.length === 0 && !checkoutStore.loading" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="text-6xl mb-4">🛒</div>
                    <h2 class="text-xl font-bold text-gray-700 mb-2">No items to checkout</h2>
                    <p class="text-gray-500 mb-6">Add some courses to your cart first</p>
                    <router-link to="/" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition-all shadow-lg hover:shadow-xl">
                        Browse Courses
                    </router-link>
                </div>

                <!-- Checkout Content -->
                <div v-else class="lg:flex gap-8">
                    <!-- Left Column - Order Items & Payment -->
                    <div class="flex-1 space-y-6">
                        <!-- Order Summary Card -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600">
                                <h2 class="text-lg font-bold text-white">Order Summary</h2>
                                <p class="text-purple-200 text-sm">{{ checkoutStore.items.length }} course(s)</p>
                            </div>
                            
                            <div class="divide-y divide-gray-100">
                                <div v-for="item in checkoutStore.items" :key="item.id" class="p-4 flex gap-4 hover:bg-gray-50 transition-colors">
                                    <div class="w-24 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gray-200">
                                        <img :src="item.thumbnail" class="w-full h-full object-cover" :alt="item.title">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 line-clamp-1">{{ item.title }}</h3>
                                        <p class="text-sm text-gray-500">By {{ item.instructor_name }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <div class="font-bold text-purple-600">£{{ parseFloat(item.price).toFixed(2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Already Enrolled Warning -->
                        <div v-if="checkoutStore.alreadyEnrolled.length > 0" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">⚠️</span>
                                <div>
                                    <h3 class="font-semibold text-amber-800">Already Enrolled</h3>
                                    <p class="text-sm text-amber-700 mt-1">
                                        You're already enrolled in: 
                                        <span class="font-medium">{{ checkoutStore.alreadyEnrolled.map(c => c.title).join(', ') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-lg font-bold text-gray-900">Payment Method</h2>
                            </div>
                            
                            <div class="p-6">
                                <!-- Loading payment methods -->
                                <div v-if="paymentStore.loading" class="flex justify-center py-4">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                                </div>

                                <!-- Payment Methods List -->
                                <div v-else class="space-y-3">
                                    <!-- Saved Payment Methods -->
                                    <label v-for="method in paymentStore.paymentMethods" :key="method.id" 
                                           class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all"
                                           :class="selectedPaymentMethod === method.id ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'">
                                        <input type="radio" :value="method.id" v-model="selectedPaymentMethod" class="sr-only">
                                        <div class="flex-shrink-0 w-12 h-8 bg-gradient-to-br from-gray-700 to-gray-900 rounded flex items-center justify-center text-white text-xs font-bold">
                                            <span v-if="method.type === 'paypal'">PP</span>
                                            <span v-else>{{ method.brand?.substring(0, 4).toUpperCase() || 'CARD' }}</span>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <template v-if="method.type === 'paypal'">
                                                <p class="font-medium text-gray-900">PayPal</p>
                                                <p class="text-sm text-gray-500">{{ method.email }}</p>
                                            </template>
                                            <template v-else>
                                                <p class="font-medium text-gray-900">{{ method.brand }} •••• {{ method.last4 }}</p>
                                                <p class="text-sm text-gray-500">Expires {{ method.exp_month }}/{{ method.exp_year }}</p>
                                            </template>
                                        </div>
                                        <div v-if="selectedPaymentMethod === method.id" class="text-purple-600">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </label>

                                    <!-- New Payment Method Option -->
                                    <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all"
                                           :class="selectedPaymentMethod === 'new' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'">
                                        <input type="radio" value="new" v-model="selectedPaymentMethod" class="sr-only">
                                        <div class="flex-shrink-0 w-12 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 rounded flex items-center justify-center text-white">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <p class="font-medium text-gray-900">Use a new payment method</p>
                                            <p class="text-sm text-gray-500">Credit/Debit card or PayPal</p>
                                        </div>
                                        <div v-if="selectedPaymentMethod === 'new'" class="text-purple-600">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </label>

                                    <!-- No Saved Methods -->
                                    <div v-if="paymentStore.paymentMethods.length === 0 && !paymentStore.loading" class="text-center py-4 text-gray-500">
                                        <p class="text-sm">No saved payment methods</p>
                                        <router-link to="/payment-methods" class="text-purple-600 hover:underline text-sm font-medium">
                                            Add a payment method
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Billing Summary -->
                    <div class="w-full lg:w-96 flex-shrink-0 mt-6 lg:mt-0">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 sticky top-24 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-gray-900 to-gray-800">
                                <h2 class="text-lg font-bold text-white">Order Total</h2>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span>£{{ checkoutStore.subtotal.toFixed(2) }}</span>
                                </div>
                                
                                <div class="flex justify-between text-gray-400 text-sm">
                                    <span>Original Price</span>
                                    <span class="line-through">£{{ (checkoutStore.subtotal * 1.5).toFixed(2) }}</span>
                                </div>

                                <div class="flex justify-between text-green-600 text-sm">
                                    <span>Discount</span>
                                    <span>-£{{ (checkoutStore.subtotal * 0.5).toFixed(2) }}</span>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xl font-bold text-gray-900">Total</span>
                                        <span class="text-3xl font-bold text-purple-600">£{{ checkoutStore.total.toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                <div v-if="checkoutStore.error" class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm">
                                    {{ checkoutStore.error }}
                                </div>

                                <!-- Complete Order Button -->
                                <button @click="completeOrder" 
                                        :disabled="!canCheckout"
                                        class="w-full py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold text-lg rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-[0.98]">
                                    <span v-if="checkoutStore.processing" class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                    <span v-else>Complete Order</span>
                                </button>

                                <!-- Validation Message -->
                                <div v-if="!selectedPaymentMethod && !paymentStore.loading" class="text-center text-amber-600 text-sm font-medium">
                                    ⚠️ Please select a payment method above
                                </div>

                                <!-- Security Badge -->
                                <div class="flex items-center justify-center gap-2 text-gray-400 text-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Secure checkout</span>
                                </div>

                                <!-- Money Back Guarantee -->
                                <div class="bg-gray-50 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-600">30-Day Money-Back Guarantee</p>
                                    <p class="text-xs text-gray-400 mt-1">Full refund if not satisfied</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useCheckoutStore } from '../stores/checkout';
import { usePaymentMethodStore } from '../stores/paymentMethod';
import Navbar from '../components/Navbar.vue';

const router = useRouter();
const cartStore = useCartStore();
const checkoutStore = useCheckoutStore();
const paymentStore = usePaymentMethodStore();

const selectedPaymentMethod = ref(null);

// Check if payment method selection is valid
const isPaymentMethodValid = computed(() => {
    // Must have a selection
    if (!selectedPaymentMethod.value) return false;
    
    // If 'new' is selected, we consider it valid (mock payment)
    if (selectedPaymentMethod.value === 'new') return true;
    
    // If a saved method is selected, verify it exists
    return paymentStore.paymentMethods.some(m => m.id === selectedPaymentMethod.value);
});

// Check if checkout can proceed
const canCheckout = computed(() => {
    return isPaymentMethodValid.value && 
           !checkoutStore.processing && 
           checkoutStore.items.length > 0;
});

onMounted(async () => {
    // Get course IDs from cart
    const courseIds = cartStore.items.map(item => item.id);
    
    if (courseIds.length === 0) {
        return;
    }

    // Fetch checkout preview and payment methods
    await Promise.all([
        checkoutStore.fetchPreview(courseIds),
        paymentStore.fetchPaymentMethods()
    ]);

    // Auto-select first saved payment method if available
    // Don't auto-select 'new' - user must explicitly choose
    if (paymentStore.paymentMethods.length > 0) {
        selectedPaymentMethod.value = paymentStore.paymentMethods[0].id;
    }
    // Leave selectedPaymentMethod as null if no saved methods
});

const completeOrder = async () => {
    if (!isPaymentMethodValid.value) {
        alert('Please select a valid payment method');
        return;
    }

    checkoutStore.selectedPaymentMethodId = selectedPaymentMethod.value === 'new' ? null : selectedPaymentMethod.value;

    try {
        const courseIds = cartStore.items.map(item => item.id);
        await checkoutStore.processCheckout(courseIds);
        
        // Clear cart on success
        cartStore.clear();
        
        // Navigate to success page
        router.push('/checkout/success');
    } catch (error) {
        console.error('Checkout failed:', error);
    }
};
</script>
