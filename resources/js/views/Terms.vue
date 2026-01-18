<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.terms.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p class="text-sm text-gray-500 mb-4">{{ $t('pages.terms.last_updated') }}</p>
                <div class="prose max-w-none">
                    <p>{{ $t('pages.terms.intro') }}</p>
                    <h3 class="font-bold mt-4 mb-2">{{ $t('pages.terms.accounts') }}</h3>
                    <p>{{ $t('pages.terms.accounts_desc') }}</p>
                    <h3 class="font-bold mt-4 mb-2">{{ $t('pages.terms.enrollment') }}</h3>
                    <p>{{ $t('pages.terms.enrollment_desc') }}</p>
                    <!-- More generic legal text -->
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
