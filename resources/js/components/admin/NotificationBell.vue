<template>
  <div class="relative" ref="bellRef">
    <button @click="toggleDropdown" class="p-2 mr-2 text-slate-500 hover:text-purple-600 dark:text-slate-400 dark:hover:text-purple-400 relative transition-colors">
      <Bell class="w-6 h-6" />
      <span v-if="unreadCount > 0" class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center border-2 border-white dark:border-slate-800 animate-pulse">
        {{ unreadCount }}
      </span>
    </button>

    <div v-if="isOpen" class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden z-50 animate-in fade-in slide-in-from-top-2">
      <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
        <h3 class="font-bold text-slate-800 dark:text-white">Notifications</h3>
        <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs text-purple-600 dark:text-purple-400 hover:underline">Mark all read</button>
      </div>

      <div class="max-h-[60vh] overflow-y-auto">
        <div v-if="notifications.length === 0" class="p-8 text-center text-slate-500 dark:text-slate-400">
            <BellOff class="w-8 h-8 mx-auto mb-2 opacity-20" />
            <p class="text-sm">No new notifications</p>
        </div>
        <div v-else>
            <div v-for="note in notifications" :key="note.id" 
                 :class="['p-4 border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors', { 'bg-purple-50/50 dark:bg-purple-900/10': !note.read }]"
                 @click="handleNotificationClick(note)"
            >
                <div class="flex gap-3">
                    <div :class="['w-2 h-2 mt-2 rounded-full flex-shrink-0', getTypeColor(note.type)]"></div>
                    <div>
                        <p class="text-sm text-slate-800 dark:text-white leading-tight mb-1">{{ note.message }}</p>
                        <span class="text-xs text-slate-400">{{ formatTime(note.timestamp) }}</span>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Bell, BellOff } from 'lucide-vue-next';
import axios from 'axios'; // For future polling or real API usage

const isOpen = ref(false);
const bellRef = ref(null);
const notifications = ref([
    { id: 1, message: 'New course "Vue 3 Mastery" pending approval', type: 'warning', read: false, timestamp: new Date(Date.now() - 1000 * 60 * 5) },
    { id: 2, message: 'User "John Doe" reported a review', type: 'error', read: false, timestamp: new Date(Date.now() - 1000 * 60 * 30) },
    { id: 3, message: 'System update completed successfully', type: 'success', read: true, timestamp: new Date(Date.now() - 1000 * 60 * 60 * 2) },
]);
const unreadCount = ref(2);

// Real-time listener using Laravel Echo
// Real-time listener using Laravel Echo
onMounted(() => {
    document.addEventListener('click', closeDropdown);

    if (window.Echo) {
        // Legacy or general admin channel
        window.Echo.private('admin-notifications')
            .listen('AdminNotification', (e) => {
                addNotification({
                    id: Date.now(),
                    message: e.message,
                    type: e.type,
                    action_url: e.action_url,
                    read: false,
                    timestamp: new Date(e.timestamp)
                });
            });

        // Standard Laravel Notification Channel
        const userId = window.user?.id || document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        if (userId) {
            window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    addNotification({
                        id: notification.id,
                        message: notification.message,
                        type: notification.type === 'course_submission' ? 'info' : 'info',
                        action_url: notification.action_url, // Maps from database notification data
                        read: false,
                        timestamp: new Date()
                    });
                });
        }
    } else {
        console.warn('Laravel Echo not initialized');
    }
});

const addNotification = (note) => {
    notifications.value.unshift(note);
    if (!note.read) unreadCount.value++;
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (e) => {
    if (bellRef.value && !bellRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

const markAllAsRead = () => {
    notifications.value.forEach(n => n.read = true);
    unreadCount.value = 0;
};

const handleNotificationClick = (note) => {
    if (!note.read) {
        note.read = true;
        unreadCount.value--;
    }
    // Navigate if actionUrl exists
    // if (note.actionUrl) router.push(note.actionUrl);
};

const getTypeColor = (type) => {
    switch (type) {
        case 'success': return 'bg-green-500';
        case 'warning': return 'bg-amber-500';
        case 'error': return 'bg-red-500';
        default: return 'bg-blue-500';
    }
};

const formatTime = (date) => {
    const d = new Date(date);
    const now = new Date();
    const diff = Math.floor((now - d) / 1000); // seconds
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return d.toLocaleDateString();
};



onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
    if (window.Echo) {
        window.Echo.leave('admin-notifications');
    }
});
</script>
