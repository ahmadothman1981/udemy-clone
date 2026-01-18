<template>
    <div class="min-h-screen bg-gray-50">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.partners.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p class="mb-4">{{ $t('pages.partners.desc') }}</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                    <!-- Placeholder logos -->
                    <div class="h-24 bg-gray-100 flex items-center justify-center rounded">Partner 1</div>
                    <div class="h-24 bg-gray-100 flex items-center justify-center rounded">Partner 2</div>
                    <div class="h-24 bg-gray-100 flex items-center justify-center rounded">Partner 3</div>
                    <div class="h-24 bg-gray-100 flex items-center justify-center rounded">Partner 4</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
</script>
