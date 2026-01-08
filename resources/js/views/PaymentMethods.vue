<template>
    <div>
        <Navbar />
        <div class="bg-gray-50 min-h-screen py-10">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Payment Methods</h1>

                <!-- Add New Card Form -->
                <div class="bg-white shadow sm:rounded-lg mb-8">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Card</h3>
                        <div class="mt-2 max-w-xl text-sm text-gray-500">
                            <p>This is a mock payment form. No real transaction occurs.</p>
                        </div>
                        <div class="mb-5">
                             <div class="flex items-center space-x-6">
                                <label class="flex items-center">
                                     <input type="radio" v-model="form.type" value="card" class="focus:ring-purple-500 h-4 w-4 text-purple-600 border-gray-300">
                                     <span class="ml-2 text-gray-700">Credit / Debit Card</span>
                                </label>
                                 <label class="flex items-center">
                                     <input type="radio" v-model="form.type" value="paypal" class="focus:ring-purple-500 h-4 w-4 text-purple-600 border-gray-300">
                                     <span class="ml-2 text-gray-700">PayPal</span>
                                </label>
                             </div>
                        </div>

                        <form @submit.prevent="addCard" class="mt-5">
                            <div v-if="form.type === 'card'" class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700">Card Brand</label>
                                    <select v-model="form.brand" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                        <option value="Visa">Visa</option>
                                        <option value="Mastercard">Mastercard</option>
                                        <option value="Amex">American Express</option>
                                    </select>
                                </div>

                                <div class="col-span-6 sm:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700">Card Number (Last 4 digits for demo)</label>
                                    <input type="text" v-model="form.last4" maxlength="4" pattern="\d{4}" required placeholder="4242" class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700">Expiry Month</label>
                                    <input type="number" v-model="form.exp_month" min="1" max="12" required placeholder="MM" class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700">Expiry Year</label>
                                    <input type="number" v-model="form.exp_year" :min="new Date().getFullYear()" required placeholder="YYYY" class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                             <div v-if="form.type === 'paypal'" class="grid grid-cols-6 gap-6">
                                  <div class="col-span-6 sm:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700">PayPal Email</label>
                                    <input type="email" v-model="form.email" required placeholder="you@example.com" class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                             </div>
                            
                            <div class="mt-5">
                                <button type="submit" :disabled="submitting" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                                    <span v-if="submitting">Saving...</span>
                                    <span v-else>Save {{ form.type === 'paypal' ? 'PayPal' : 'Card' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List of Cards -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Saved Cards</h3>
                    </div>
                    
                    <div v-if="paymentStore.loading" class="text-center py-10">
                         <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                    </div>

                    <div v-else-if="paymentStore.paymentMethods.length === 0" class="text-center py-10 text-gray-500">
                        No saved payment methods.
                    </div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="method in paymentStore.paymentMethods" :key="method.id" class="px-4 py-4 sm:px-6 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 font-bold text-xs">
                                     <span v-if="method.type === 'paypal'">PP</span>
                                    <span v-else>{{ method.brand ? method.brand.substring(0, 4).toUpperCase() : 'CARD' }}</span>
                                </div>
                                <div class="ml-4">
                                    <template v-if="method.type === 'paypal'">
                                        <p class="text-sm font-medium text-gray-900">PayPal</p>
                                        <p class="text-sm text-gray-500">{{ method.email }}</p>
                                    </template>
                                    <template v-else>
                                        <p class="text-sm font-medium text-gray-900">{{ method.brand }} ending in {{ method.last4 }}</p>
                                        <p class="text-sm text-gray-500">Expires {{ method.exp_month }}/{{ method.exp_year }}</p>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <button @click="deleteCard(method.id)" class="text-red-600 hover:text-red-900 text-sm font-medium">Remove</button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { usePaymentMethodStore } from '../stores/paymentMethod';
import Navbar from '../components/Navbar.vue';

const paymentStore = usePaymentMethodStore();
const submitting = ref(false);

const form = reactive({
    type: 'card',
    email: '',
    brand: 'Visa',
    last4: '',
    exp_month: '',
    exp_year: ''
});

onMounted(() => {
    paymentStore.fetchPaymentMethods();
});

const addCard = async () => {
    submitting.value = true;
    try {
        await paymentStore.addPaymentMethod(form);
        // Reset form
        form.last4 = '';
        form.exp_month = '';
        form.exp_year = '';
        form.email = '';
        alert(form.type === 'paypal' ? 'PayPal account linked successfully' : 'Card added successfully');
    } catch (e) {
        alert('Failed to add payment method');
    } finally {
        submitting.value = false;
    }
};

const deleteCard = async (id) => {
    if (!confirm('Are you sure you want to remove this card?')) return;
    try {
        await paymentStore.deletePaymentMethod(id);
    } catch (e) {
        alert('Failed to remove card');
    }
};
</script>
