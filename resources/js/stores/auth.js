import { defineStore } from 'pinia';
import axios from 'axios';
import { useCartStore } from './cart';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
        isInstructor: (state) => state.user?.roles?.some(r => r.name === 'instructor') || false,
        isAdmin: (state) => state.user?.roles?.some(r => r.name === 'admin') || false,
    },
    actions: {
        async register(userData) {
            try {
                const response = await axios.post('/api/register', userData);
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('token', this.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            } catch (e) {
                throw e;
            }
        },
        async login(email, password) {
            try {
                const response = await axios.post('/api/login', { email, password });
                this.token = response.data.token;
                this.user = response.data.user;
                localStorage.setItem('token', this.token);

                // set default axios header
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            } catch (e) {
                throw e;
            }
        },
        async fetchUser() {
            if (!this.token) return;
            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                const response = await axios.get('/api/me');
                this.user = response.data.data;
            } catch (e) {
                this.logout();
            }
        },
        async updateProfile(data) {
            try {
                const response = await axios.put('/api/profile', data);
                this.user = response.data.data;
                return response.data;
            } catch (e) {
                throw e;
            }
        },
        async logout() {
            // Call server to revoke token
            try {
                await axios.post('/api/logout');
            } catch (e) {
                // Ignore errors - clear local state anyway
            }

            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];

            // Clear cart
            const cartStore = useCartStore();
            cartStore.clear();
        }
    }
});
