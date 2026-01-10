<template>
    <button @click="toggleLocale" 
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <span class="text-lg">{{ currentFlag }}</span>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ currentLabel }}
        </span>
    </button>
</template>

<script setup>
import { computed } from 'vue';
import { useLocaleStore } from '../stores/locale';

const localeStore = useLocaleStore();

const currentFlag = computed(() => {
    return localeStore.currentLocale === 'en' ? '🇬🇧' : '🇸🇦';
});

const currentLabel = computed(() => {
    return localeStore.currentLocale === 'en' ? 'EN' : 'عربي';
});

const toggleLocale = () => {
    localeStore.toggleLocale();
};
</script>
