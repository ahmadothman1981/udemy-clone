<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.blog.title') }}</h1>
            <div class="grid gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h2 class="text-xl font-bold mb-2">{{ $t('pages.blog.post_title') }}</h2>
                    <p class="text-gray-500 text-sm mb-4">{{ $t('pages.blog.post_date') }}</p>
                    <p>{{ $t('pages.blog.post_excerpt') }}</p>
                </div>
                <!-- More placeholders -->
            </div>
        </div>
        <Footer />
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
</script>
