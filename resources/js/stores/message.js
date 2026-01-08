import { defineStore } from 'pinia';
import axios from 'axios';

export const useMessageStore = defineStore('message', {
    state: () => ({
        conversations: [],
        currentConversation: [],
        loadingConversations: false,
        loadingMessages: false,
        activeUserId: null,
    }),
    actions: {
        async fetchConversations() {
            this.loadingConversations = true;
            try {
                const response = await axios.get('/api/messages');
                this.conversations = response.data.data;
            } catch (error) {
                console.error('Failed to fetch conversations', error);
            } finally {
                this.loadingConversations = false;
            }
        },
        async fetchMessages(userId) {
            this.activeUserId = userId;
            this.loadingMessages = true;
            try {
                const response = await axios.get(`/api/messages/${userId}`);
                this.currentConversation = response.data.data;
                // Update read status locally
                const userConv = this.conversations.find(c => c.id === userId);
                if (userConv && userConv.last_message) {
                    // This is a simplification; ideally we'd mark specific messages
                }
            } catch (error) {
                console.error('Failed to fetch messages', error);
            } finally {
                this.loadingMessages = false;
            }
        },
        async sendMessage(receiverId, content) {
            try {
                const response = await axios.post('/api/messages', {
                    receiver_id: receiverId,
                    content: content
                });
                const newMessage = response.data.data;

                // Add to current conversation if it's open
                if (this.activeUserId === receiverId) {
                    this.currentConversation.push(newMessage);
                }

                // Refresh conversations to update last message snippet
                await this.fetchConversations();
                return newMessage;
            } catch (error) {
                throw error;
            }
        }
    }
});
