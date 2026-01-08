import { defineStore } from 'pinia';
import axios from 'axios';

export const usePaymentMethodStore = defineStore('paymentMethod', {
    state: () => ({
        paymentMethods: [],
        loading: false,
    }),
    actions: {
        async fetchPaymentMethods() {
            this.loading = true;
            try {
                const response = await axios.get('/api/payment-methods');
                this.paymentMethods = response.data.data;
            } catch (error) {
                console.error('Failed to fetch payment methods', error);
            } finally {
                this.loading = false;
            }
        },
        async addPaymentMethod(data) {
            try {
                const response = await axios.post('/api/payment-methods', data);
                this.paymentMethods.unshift(response.data.data);
                return response.data;
            } catch (error) {
                throw error;
            }
        },
        async deletePaymentMethod(id) {
            try {
                await axios.delete(`/api/payment-methods/${id}`);
                this.paymentMethods = this.paymentMethods.filter(pm => pm.id !== id);
            } catch (error) {
                throw error;
            }
        }
    }
});
