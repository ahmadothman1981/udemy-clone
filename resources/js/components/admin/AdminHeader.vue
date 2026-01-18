<template>
  <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 h-16 flex items-center justify-between px-8 sticky top-0 z-30 transition-colors duration-300">
      <h1 class="text-xl font-bold text-slate-800 dark:text-white">{{ title }}</h1>
      <div class="flex items-center gap-4">
            <slot name="actions"></slot>
            <!-- Language Toggle -->
            <button @click="toggleLang" class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors font-bold text-sm uppercase" title="Switch Language">
                {{ locale === 'en' ? 'AR' : 'EN' }}
            </button>

            <!-- Dark Mode Toggle -->
            <button @click="toggleDark" class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" :title="$t('admin.header.toggle_dark')">
                <Sun v-if="!isDark" class="w-5 h-5 text-yellow-500" />
                <Moon v-else class="w-5 h-5 text-purple-400" />
            </button>

            <NotificationBell />
            
            <div class="flex flex-col items-end mr-2">
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ auth.user?.name }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ $t('admin.header.administrator') }}</span>
            </div>
            
            <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                {{ auth.user?.name?.[0] || 'A' }}
            </div>

            <!-- Sign Out -->
            <button @click="$emit('logout')" class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" :title="$t('admin.header.sign_out')">
                <LogOut class="w-5 h-5" />
            </button>
       </div>
  </header>
</template>

<script setup>
import { Sun, Moon, LogOut } from 'lucide-vue-next';
import { useDarkMode } from '../../composables/useDarkMode';
import { useAuthStore } from '../../stores/auth';
import { useI18n } from 'vue-i18n';
import NotificationBell from './NotificationBell.vue';

defineProps({
    title: {
        type: String,
        required: true
    }
});

defineEmits(['logout']);

const { isDark, toggleDark } = useDarkMode();
const auth = useAuthStore();
const { locale } = useI18n();

const toggleLang = () => {
    const newLocale = locale.value === 'en' ? 'ar' : 'en';
    locale.value = newLocale;
    localStorage.setItem('locale', newLocale);
    document.documentElement.lang = newLocale;
    document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr';
};
</script>
