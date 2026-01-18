<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.about.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p class="mb-4">{{ $t('pages.about.welcome') }}</p>
                <p>{{ $t('pages.about.mission') }}</p>
            </div>
        </div>
        <Footer />
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
</script>
