<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8 flex-grow w-full">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.cookie.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                 <div class="prose max-w-none">
                    <p>{{ $t('pages.cookie.intro') }}</p>
                    <h3 class="font-bold mt-4 mb-2">{{ $t('pages.cookie.what_are') }}</h3>
                    <p>{{ $t('pages.cookie.what_are_desc') }}</p>
                    
                    <div class="mt-8 border-t pt-4">
                        <h4 class="font-bold mb-4">{{ $t('pages.cookie.manage') }}</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-bold">{{ $t('pages.cookie.essential') }}</div>
                                    <div class="text-sm text-gray-500">{{ $t('pages.cookie.essential_desc') }}</div>
                                </div>
                                <input type="checkbox" checked disabled>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-bold">{{ $t('pages.cookie.analytics') }}</div>
                                    <div class="text-sm text-gray-500">{{ $t('pages.cookie.analytics_desc') }}</div>
                                </div>
                                <input type="checkbox">
                            </div>
                        </div>
                         <button class="mt-6 px-6 py-2 bg-purple-600 text-white font-bold rounded hover:bg-purple-700">{{ $t('pages.cookie.save') }}</button>
                    </div>
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
