<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.contact.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm max-w-2xl">
                <form @submit.prevent class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('pages.contact.name') }}</label>
                        <input type="text" class="w-full px-3 py-2 border rounded focus:ring-purple-500 focus:border-purple-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('pages.contact.email') }}</label>
                        <input type="email" class="w-full px-3 py-2 border rounded focus:ring-purple-500 focus:border-purple-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $t('pages.contact.message') }}</label>
                        <textarea rows="4" class="w-full px-3 py-2 border rounded focus:ring-purple-500 focus:border-purple-500" required></textarea>
                    </div>
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded font-bold hover:bg-purple-700">{{ $t('pages.contact.send') }}</button>
                </form>
            </div>
        </div>
        <Footer />
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
</script>
