import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';

const showHelpModal = ref(false);

export function useKeyboardShortcuts() {
    const router = useRouter();
    const lastKeyTime = ref(0);
    const lastKey = ref(null);

    const shortcuts = [
        { keys: ['g', 'd'], action: () => router.push('/admin/dashboard'), description: 'Go to Dashboard' },
        { keys: ['g', 'u'], action: () => router.push('/admin/users'), description: 'Go to Users' },
        { keys: ['g', 'c'], action: () => router.push('/admin/courses'), description: 'Go to Courses' },
        { keys: ['g', 'i'], action: () => router.push('/admin/instructors'), description: 'Go to Instructors' },
        { keys: ['g', 'r'], action: () => router.push('/admin/reviews'), description: 'Go to Reviews' },
        { keys: ['g', 'q'], action: () => router.push('/admin/questions'), description: 'Go to Q&A' },
        { keys: ['g', 's'], action: () => router.push('/admin/settings'), description: 'Go to Settings' },
        { keys: ['?'], action: () => showHelpModal.value = !showHelpModal.value, description: 'Toggle Shortcuts Help' },
        { keys: ['Escape'], action: () => showHelpModal.value = false, description: 'Close Help' }
    ];

    const handleKeydown = (e) => {
        // Ignore if typing in input/textarea/editable
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || e.target.isContentEditable) {
            if (e.key === 'Escape') e.target.blur();
            return;
        }

        const now = Date.now();
        const char = e.key;

        // Handle single key shortcuts (like ?)
        if (char === '?') {
            const shortcut = shortcuts.find(s => s.keys.length === 1 && s.keys[0] === '?');
            if (shortcut) {
                e.preventDefault();
                shortcut.action();
                return;
            }
        }

        if (e.key === 'Escape') {
            showHelpModal.value = false;
            return;
        }

        // Handle combinations (g + key)
        if (lastKey.value && (now - lastKeyTime.value < 500)) {
            const combo = [lastKey.value, char];
            const shortcut = shortcuts.find(s =>
                s.keys.length === 2 &&
                s.keys[0] === combo[0] &&
                s.keys[1] === combo[1]
            );

            if (shortcut) {
                e.preventDefault();
                shortcut.action();
                lastKey.value = null; // Reset
                return;
            }
        }

        // Start combo?
        if (char === 'g') {
            lastKey.value = 'g';
            lastKeyTime.value = now;
        } else {
            lastKey.value = null;
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handleKeydown);
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handleKeydown);
    });

    return {
        showHelpModal,
        shortcuts: shortcuts.filter(s => s.description !== 'Close Help')
    };
}
