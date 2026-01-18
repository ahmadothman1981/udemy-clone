<template>
    <div class="min-h-screen bg-gray-50">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.affiliate.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p class="mb-4">{{ $t('pages.affiliate.earn') }}</p>
                <p>{{ $t('pages.affiliate.desc') }}</p>
                <button class="mt-4 px-6 py-2 bg-purple-600 text-white font-bold rounded hover:bg-purple-700">{{ $t('pages.affiliate.join') }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
</script>
