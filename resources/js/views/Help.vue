<template>
    <div class="min-h-screen bg-gray-50">
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">{{ $t('pages.help.title') }}</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="max-w-xl mx-auto text-center mb-10">
                    <h2 class="text-xl font-bold mb-4">{{ $t('pages.help.how_help') }}</h2>
                    <input type="text" :placeholder="$t('pages.help.search_placeholder')" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none">
                </div>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="p-4 border rounded hover:shadow-md transition">
                        <h3 class="font-bold mb-2">{{ $t('pages.help.student_support') }}</h3>
                        <p class="text-sm text-gray-600">{{ $t('pages.help.student_desc') }}</p>
                    </div>
                     <div class="p-4 border rounded hover:shadow-md transition">
                        <h3 class="font-bold mb-2">{{ $t('pages.help.instructor_support') }}</h3>
                        <p class="text-sm text-gray-600">{{ $t('pages.help.instructor_desc') }}</p>
                    </div>
                     <div class="p-4 border rounded hover:shadow-md transition">
                        <h3 class="font-bold mb-2">{{ $t('pages.help.trust_safety') }}</h3>
                        <p class="text-sm text-gray-600">{{ $t('pages.help.trust_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue';
</script>
