<template>
    <div>
        <Navbar />
        <div class="bg-gray-50 min-h-screen py-10">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
                    <button 
                        v-if="notificationStore.unreadCount > 0"
                        @click="notificationStore.markAllAsRead"
                        class="text-sm text-purple-600 hover:text-purple-800 font-medium"
                    >
                        Mark all as read
                    </button>
                </div>

                <div v-if="notificationStore.loading" class="text-center py-10">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                </div>

                <div v-else-if="notificationStore.notifications.length === 0" class="text-center py-20 bg-white rounded-lg shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="text-lg font-bold text-gray-700">No notifications</h3>
                    <p class="text-gray-500">You don't have any notifications yet.</p>
                </div>

                <div v-else class="bg-white shadow rounded-lg divide-y divide-gray-100">
                    <div 
                        v-for="notification in notificationStore.notifications" 
                        :key="notification.id"
                        class="p-4 hover:bg-gray-50 transition-colors cursor-pointer"
                        :class="{'bg-purple-50': !notification.read_at}"
                        @click="handleNotificationClick(notification)"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-0.5">
                                <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ notification.data.message || 'New Notification' }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ formatDate(notification.created_at) }}
                                </p>
                            </div>
                             <div  v-if="!notification.read_at" class="ml-auto pl-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-purple-600 block"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useNotificationStore } from '../stores/notification';
import Navbar from '../components/Navbar.vue';

const notificationStore = useNotificationStore();

onMounted(() => {
    notificationStore.fetchNotifications();
});

const handleNotificationClick = async (notification) => {
    if (!notification.read_at) {
        await notificationStore.markAsRead(notification.id);
    }
    // Handle navigation if notification has action_url
    // if (notification.data.action_url) router.push(notification.data.action_url);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};
</script>
