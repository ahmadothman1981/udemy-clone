import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
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
        logout() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
            // call api logout if needed
        }
    }
});
