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
                                        <div class="font-bold text-purple-600">${{ parseFloat(item.price).toFixed(2) }}</div>
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

                        <!-- Payment Section -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-lg font-bold text-gray-900">Payment Details</h2>
                                <p class="text-sm text-gray-500">Secure payment powered by Stripe</p>
                            </div>
                            
                            <div class="p-6">
                                <!-- Stripe Payment Element Container -->
                                <div id="payment-element" class="min-h-[120px]">
                                    <!-- Stripe Elements will be mounted here -->
                                    <div v-if="!stripeReady" class="flex justify-center py-8">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                                    </div>
                                </div>

                                <!-- Payment Errors -->
                                <div v-if="paymentError" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm">
                                    {{ paymentError }}
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
                                    <span>${{ checkoutStore.subtotal.toFixed(2) }}</span>
                                </div>
                                
                                <div class="flex justify-between text-gray-400 text-sm">
                                    <span>Original Price</span>
                                    <span class="line-through">${{ (checkoutStore.subtotal * 1.5).toFixed(2) }}</span>
                                </div>

                                <div class="flex justify-between text-green-600 text-sm">
                                    <span>Discount</span>
                                    <span>-${{ (checkoutStore.subtotal * 0.5).toFixed(2) }}</span>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xl font-bold text-gray-900">Total</span>
                                        <span class="text-3xl font-bold text-purple-600">${{ checkoutStore.total.toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                <div v-if="checkoutStore.error" class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm">
                                    {{ checkoutStore.error }}
                                </div>

                                <!-- Complete Order Button -->
                                <button @click="handlePayment" 
                                        :disabled="!canCheckout || processing"
                                        class="w-full py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold text-lg rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.02] active:scale-[0.98]">
                                    <span v-if="processing" class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing Payment...
                                    </span>
                                    <span v-else>Pay ${{ checkoutStore.total.toFixed(2) }}</span>
                                </button>

                                <!-- Security Badge -->
                                <div class="flex items-center justify-center gap-2 text-gray-400 text-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Secure payment with Stripe</span>
                                </div>

                                <!-- Test Card Info -->
                                <div class="bg-blue-50 rounded-lg p-4 text-center">
                                    <p class="text-xs text-blue-600 font-medium">Test Mode</p>
                                    <p class="text-xs text-blue-500 mt-1">Use card: 4242 4242 4242 4242</p>
                                    <p class="text-xs text-blue-400">Any future date, any CVC</p>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useCheckoutStore } from '../stores/checkout';
import Navbar from '../components/Navbar.vue';

const router = useRouter();
const cartStore = useCartStore();
const checkoutStore = useCheckoutStore();

// Stripe state
const stripe = ref(null);
const elements = ref(null);
const paymentElement = ref(null);
const stripeReady = ref(false);
const processing = ref(false);
const paymentError = ref(null);

// Get Stripe publishable key from env
const stripeKey = import.meta.env.VITE_STRIPE_KEY || 'pk_test_placeholder';

// Check if checkout can proceed
const canCheckout = computed(() => {
    return stripeReady.value && 
           !processing.value && 
           checkoutStore.items.length > 0;
});

// Load Stripe.js dynamically
const loadStripe = async () => {
    if (window.Stripe) {
        return window.Stripe;
    }
    
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://js.stripe.com/v3/';
        script.async = true;
        script.onload = () => resolve(window.Stripe);
        script.onerror = () => reject(new Error('Failed to load Stripe.js'));
        document.head.appendChild(script);
    });
};

// Initialize Stripe and Payment Element
const initializeStripe = async (clientSecret) => {
    try {
        const Stripe = await loadStripe();
        stripe.value = Stripe(stripeKey);
        
        // Create Elements instance with the client secret
        elements.value = stripe.value.elements({
            clientSecret,
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#9333ea',
                    colorBackground: '#ffffff',
                    colorText: '#1f2937',
                    colorDanger: '#dc2626',
                    fontFamily: 'system-ui, sans-serif',
                    spacingUnit: '4px',
                    borderRadius: '8px',
                },
            },
        });
        
        // Create and mount Payment Element
        paymentElement.value = elements.value.create('payment', {
            layout: 'tabs',
        });
        
        paymentElement.value.mount('#payment-element');
        
        paymentElement.value.on('ready', () => {
            stripeReady.value = true;
        });
        
        paymentElement.value.on('change', (event) => {
            if (event.error) {
                paymentError.value = event.error.message;
            } else {
                paymentError.value = null;
            }
        });
    } catch (error) {
        console.error('Failed to initialize Stripe:', error);
        paymentError.value = 'Failed to load payment form. Please refresh the page.';
    }
};

onMounted(async () => {
    // Get course IDs from cart
    const courseIds = cartStore.items.map(item => item.id);
    
    if (courseIds.length === 0) {
        return;
    }

    // Fetch checkout preview first
    await checkoutStore.fetchPreview(courseIds);
    
    // If there are items to purchase, create PaymentIntent
    if (checkoutStore.items.length > 0 && checkoutStore.total > 0) {
        try {
            const result = await checkoutStore.createPaymentIntent(courseIds);
            
            // Check if it was a free enrollment
            if (result.success) {
                cartStore.clear();
                router.push('/checkout/success');
                return;
            }
            
            // Initialize Stripe with clientSecret
            if (result.clientSecret) {
                await initializeStripe(result.clientSecret);
            }
        } catch (error) {
            console.error('Failed to create payment intent:', error);
        }
    } else if (checkoutStore.items.length > 0 && checkoutStore.total === 0) {
        // Free courses - enroll directly
        try {
            const result = await checkoutStore.createPaymentIntent(courseIds);
            if (result.success) {
                cartStore.clear();
                router.push('/checkout/success');
            }
        } catch (error) {
            console.error('Failed to enroll in free courses:', error);
        }
    }
});

onUnmounted(() => {
    // Cleanup Stripe elements
    if (paymentElement.value) {
        paymentElement.value.unmount();
    }
});

const handlePayment = async () => {
    if (!stripe.value || !elements.value) {
        paymentError.value = 'Payment form not ready. Please wait or refresh.';
        return;
    }
    
    processing.value = true;
    paymentError.value = null;

    try {
        const { error, paymentIntent } = await stripe.value.confirmPayment({
            elements: elements.value,
            confirmParams: {
                return_url: `${window.location.origin}/checkout/success`,
            },
            redirect: 'if_required',
        });

        if (error) {
            // Show error to customer
            paymentError.value = error.message;
            processing.value = false;
        } else if (paymentIntent && paymentIntent.status === 'succeeded') {
            // Payment succeeded
            checkoutStore.setPaymentSuccess({
                orderId: checkoutStore.orderId,
                status: 'paid',
            });
            cartStore.clear();
            router.push('/checkout/success');
        } else if (paymentIntent && paymentIntent.status === 'requires_action') {
            // 3D Secure or other action required - Stripe will handle redirect
            paymentError.value = 'Additional authentication required...';
        } else {
            // Unexpected status
            paymentError.value = 'Payment processing. Please check your order status.';
            processing.value = false;
        }
    } catch (err) {
        console.error('Payment error:', err);
        paymentError.value = 'An unexpected error occurred. Please try again.';
        processing.value = false;
    }
};
</script>
