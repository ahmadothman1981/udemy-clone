<template>
    <div class="min-h-screen bg-gray-50">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.careers.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p class="mb-4">{{ $t('pages.careers.subtitle') }}</p>
                <p>{{ $t('pages.careers.text') }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
</script>
