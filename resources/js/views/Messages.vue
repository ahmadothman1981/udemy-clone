<template>
    <div>
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 h-[calc(100vh-64px)]">
            <div class="flex h-full bg-white shadow rounded-lg overflow-hidden">
                <!-- Sidebar: Conversations List -->
                <div class="w-1/3 border-r border-gray-200 flex flex-col">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Messages</h2>
                    </div>
                    
                    <div class="overflow-y-auto flex-1">
                        <div v-if="messageStore.loadingConversations" class="p-4 text-center">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                        </div>
                        <div v-else-if="messageStore.conversations.length === 0" class="p-4 text-center text-gray-500">
                            No conversations yet.
                        </div>
                        <ul v-else class="divide-y divide-gray-200">
                            <li 
                                v-for="user in messageStore.conversations" 
                                :key="user.id"
                                @click="selectUser(user.id)"
                                class="p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                                :class="{'bg-purple-50': messageStore.activeUserId === user.id}"
                            >
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold">
                                            {{ user.name[0] }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ user.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ user.last_message ? user.last_message.content : 'No messages' }}
                                        </p>
                                    </div>
                                    <div v-if="user.last_message" class="text-xs text-gray-400 whitespace-nowrap">
                                        {{ formatDate(user.last_message.created_at) }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Main Chat Area -->
                <div class="flex-1 flex flex-col bg-gray-50">
                    <div v-if="!messageStore.activeUserId" class="flex-1 flex items-center justify-center text-gray-500">
                        Select a conversation to start messaging
                    </div>
                    <template v-else>
                        <!-- Messages Display -->
                        <div class="flex-1 p-4 overflow-y-auto" ref="messagesContainer">
                            <div v-if="messageStore.loadingMessages" class="text-center py-4">
                                <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                            </div>
                             <div v-else class="space-y-4">
                                <div 
                                    v-for="msg in messageStore.currentConversation" 
                                    :key="msg.id"
                                    class="flex"
                                    :class="msg.sender_id === authStore.user.id ? 'justify-end' : 'justify-start'"
                                >
                                    <div 
                                        class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg shadow-sm"
                                        :class="msg.sender_id === authStore.user.id 
                                            ? 'bg-purple-600 text-white rounded-br-none' 
                                            : 'bg-white text-gray-900 rounded-bl-none'"
                                    >
                                        <p class="text-sm">{{ msg.content }}</p>
                                        <p class="text-xs mt-1" :class="msg.sender_id === authStore.user.id ? 'text-purple-200' : 'text-gray-400'">
                                            {{ formatTime(msg.created_at) }}
                                        </p>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- Input Area -->
                        <div class="p-4 bg-white border-t border-gray-200">
                            <form @submit.prevent="sendMessage" class="flex space-x-4">
                                <input 
                                    v-model="newMessage"
                                    type="text" 
                                    placeholder="Type a message..." 
                                    class="flex-1 focus:ring-purple-500 focus:border-purple-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    required
                                >
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                    :disabled="sending"
                                >
                                    Send
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useMessageStore } from '../stores/message';
import { useAuthStore } from '../stores/auth';
import Navbar from '../components/Navbar.vue';

const messageStore = useMessageStore();
const authStore = useAuthStore();
const newMessage = ref('');
const sending = ref(false);
const messagesContainer = ref(null);

onMounted(() => {
    messageStore.fetchConversations();
});

const selectUser = async (userId) => {
    await messageStore.fetchMessages(userId);
    scrollToBottom();
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !messageStore.activeUserId) return;
    
    sending.value = true;
    try {
        await messageStore.sendMessage(messageStore.activeUserId, newMessage.value);
        newMessage.value = '';
        scrollToBottom();
    } catch (e) {
        alert('Failed to send message');
    } finally {
        sending.value = false;
    }
};

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric' }).format(date);
};

const formatTime = (dateString) => {
     const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', { hour: '2-digit', minute: '2-digit' }).format(date);
};
</script>
