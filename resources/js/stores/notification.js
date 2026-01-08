import { defineStore } from 'pinia';
import axios from 'axios';

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        loading: false,
    }),
    getters: {
        unreadCount: (state) => state.notifications.filter(n => !n.read_at).length,
    },
    actions: {
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await axios.get('/api/notifications');
                this.notifications = response.data.data;
            } catch (error) {
                console.error('Failed to fetch notifications', error);
            } finally {
                this.loading = false;
            }
        },
        async markAsRead(id) {
            try {
                await axios.post(`/api/notifications/${id}/read`);
                const notification = this.notifications.find(n => n.id === id);
                if (notification) {
                    notification.read_at = new Date().toISOString();
                }
            } catch (error) {
                console.error('Failed to mark notification as read', error);
            }
        },
        async markAllAsRead() {
            try {
                await axios.post('/api/notifications/read-all');
                this.notifications.forEach(n => {
                    n.read_at = new Date().toISOString();
                });
            } catch (error) {
                console.error('Failed to mark all as read', error);
            }
        }
    }
});
