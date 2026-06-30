import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'kids' | 'elegant' | 'forest' | 'system';

export function updateTheme(value: Appearance) {
    // Removemos las clases específicas de apariencia antes de aplicar la nueva
    document.documentElement.classList.remove('dark', 'kids', 'elegant', 'forest');

    if (value === 'system') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else if (value === 'dark') {
        document.documentElement.classList.add('dark');
    } else if (value === 'kids') {
        document.documentElement.classList.add('kids');
    } else if (value === 'elegant') {
        document.documentElement.classList.add('elegant');
    } else if (value === 'forest') {
        document.documentElement.classList.add('forest');
    }
}

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const handleSystemThemeChange = () => {
    const currentAppearance = localStorage.getItem('appearance') as Appearance | null;
    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    // Initialize theme from saved preference or default to system...
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
    updateTheme(savedAppearance || 'system');

    // Set up system theme change listener...
    mediaQuery.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    const appearance = ref<Appearance>('system');

    onMounted(() => {
        initializeTheme();

        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        localStorage.setItem('appearance', value);
        updateTheme(value);
    }

    return {
        appearance,
        updateAppearance,
    };
}
