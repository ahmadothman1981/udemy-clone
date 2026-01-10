import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
import i18n from '../locales';

export const useLocaleStore = defineStore('locale', () => {
    const currentLocale = ref(localStorage.getItem('locale') || 'en');

    const isRTL = computed(() => currentLocale.value === 'ar');

    const direction = computed(() => isRTL.value ? 'rtl' : 'ltr');

    function setLocale(locale) {
        currentLocale.value = locale;
        localStorage.setItem('locale', locale);
        i18n.global.locale.value = locale;

        // Update document direction
        document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.lang = locale;

        // Add/remove RTL class for Tailwind
        if (locale === 'ar') {
            document.documentElement.classList.add('rtl');
        } else {
            document.documentElement.classList.remove('rtl');
        }
    }

    function toggleLocale() {
        setLocale(currentLocale.value === 'en' ? 'ar' : 'en');
    }

    // Initialize on load
    function init() {
        setLocale(currentLocale.value);
    }

    return {
        currentLocale,
        isRTL,
        direction,
        setLocale,
        toggleLocale,
        init,
    };
});
