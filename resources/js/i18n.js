import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import ar from './locales/ar.json';

const savedLocale = localStorage.getItem('locale') || 'en';

const i18n = createI18n({
    legacy: false, // Usage with Composition API
    locale: savedLocale,
    fallbackLocale: 'en',
    messages: {
        en,
        ar
    }
});

// Set initial direction
const setDirection = (locale) => {
    document.documentElement.lang = locale;
    document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
};
setDirection(savedLocale);


export default i18n;
