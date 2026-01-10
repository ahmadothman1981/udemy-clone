import { defineStore } from 'pinia';
import axios from 'axios';

export const useCheckoutStore = defineStore('checkout', {
    state: () => ({
        items: [],
        alreadyEnrolled: [],
        subtotal: 0,
        total: 0,
        selectedPaymentMethodId: null,
        loading: false,
        processing: false,
        error: null,
        orderResult: null,
        // Stripe-specific state
        clientSecret: null,
        orderId: null,
    }),
    actions: {
        async fetchPreview(courseIds) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.post('/api/checkout/preview', {
                    course_ids: courseIds,
                });
                this.items = response.data.items;
                this.alreadyEnrolled = response.data.already_enrolled || [];
                this.subtotal = response.data.subtotal;
                this.total = response.data.total;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to load checkout';
                console.error('Checkout preview error:', error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Create PaymentIntent and get clientSecret for Stripe Elements
         */
        async createPaymentIntent(courseIds) {
            this.processing = true;
            this.error = null;
            this.clientSecret = null;
            this.orderId = null;

            try {
                const response = await axios.post('/api/checkout', {
                    course_ids: courseIds,
                });

                // Check if free enrollment (no payment needed)
                if (response.data.success) {
                    this.orderResult = response.data;
                    return { success: true, order: response.data.order };
                }

                // Paid course - get clientSecret for Stripe
                this.clientSecret = response.data.clientSecret;
                this.orderId = response.data.order_id;

                return {
                    clientSecret: this.clientSecret,
                    orderId: this.orderId,
                    total: response.data.total
                };
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create payment';
                throw error;
            } finally {
                this.processing = false;
            }
        },

        /**
         * Called after Stripe confirms payment
         * The webhook will handle enrollment creation
         */
        setPaymentSuccess(orderData) {
            this.orderResult = orderData;
            this.clientSecret = null;
            this.orderId = null;
        },

        setPaymentError(message) {
            this.error = message;
            this.clientSecret = null;
        },

        reset() {
            this.items = [];
            this.alreadyEnrolled = [];
            this.subtotal = 0;
            this.total = 0;
            this.selectedPaymentMethodId = null;
            this.error = null;
            this.orderResult = null;
            this.clientSecret = null;
            this.orderId = null;
        }
    }
});
