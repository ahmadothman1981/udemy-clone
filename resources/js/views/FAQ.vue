<template>
    <div class="min-h-screen bg-gray-50">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.faq.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm space-y-6">
                <div>
                    <h3 class="font-bold text-lg mb-2">{{ $t('pages.faq.q1') }}</h3>
                    <p class="text-gray-600">{{ $t('pages.faq.a1') }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-2">{{ $t('pages.faq.q2') }}</h3>
                    <p class="text-gray-600">{{ $t('pages.faq.a2') }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-2">{{ $t('pages.faq.q3') }}</h3>
                    <p class="text-gray-600">{{ $t('pages.faq.a3') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
</script>
