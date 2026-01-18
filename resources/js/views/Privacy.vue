<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.privacy.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p class="text-sm text-gray-500 mb-4">{{ $t('pages.privacy.last_updated') }}</p>
                 <div class="prose max-w-none">
                    <p>{{ $t('pages.privacy.intro') }}</p>
                    <h3 class="font-bold mt-4 mb-2">{{ $t('pages.privacy.collect') }}</h3>
                    <p>{{ $t('pages.privacy.collect_desc') }}</p>
                    <h3 class="font-bold mt-4 mb-2">{{ $t('pages.privacy.use') }}</h3>
                    <p>{{ $t('pages.privacy.use_desc') }}</p>
                </div>
            </div>
        </div>
        <Footer />
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
</script>
