import { ref, onMounted, watch } from 'vue';

const isDark = ref(localStorage.getItem('theme') === 'dark');

export function useDarkMode() {
    const toggleDark = () => {
        isDark.value = !isDark.value;
        updateTheme();
    };

    const updateTheme = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    };

    onMounted(() => {
        // Check system preference if no local storage
        if (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            isDark.value = true;
        }
        updateTheme();
    });

    return {
        isDark,
        toggleDark
    };
}
