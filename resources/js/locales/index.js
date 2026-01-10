import { createI18n } from 'vue-i18n';
import en from './en';
import ar from './ar';

const i18n = createI18n({
    legacy: false, // Use Composition API
    locale: localStorage.getItem('locale') || 'en',
    fallbackLocale: 'en',
    messages: {
        en,
        ar,
    },
});

export default i18n;
