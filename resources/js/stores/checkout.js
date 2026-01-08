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

        async processCheckout(courseIds) {
            this.processing = true;
            this.error = null;
            this.orderResult = null;
            try {
                const response = await axios.post('/api/checkout', {
                    course_ids: courseIds,
                    payment_method_id: this.selectedPaymentMethodId,
                });
                this.orderResult = response.data;
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Checkout failed';
                throw error;
            } finally {
                this.processing = false;
            }
        },

        reset() {
            this.items = [];
            this.alreadyEnrolled = [];
            this.subtotal = 0;
            this.total = 0;
            this.selectedPaymentMethodId = null;
            this.error = null;
            this.orderResult = null;
        }
    }
});
