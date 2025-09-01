import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

export function updateTheme(value: Appearance) {
    if (value === 'system') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const handleSystemThemeChange = () => {
    const currentAppearance = localStorage.getItem('appearance') as Appearance | null;
    if (!currentAppearance) {
        // Only follow system if no preference is saved
        const systemTheme = mediaQuery.matches ? 'dark' : 'light';
        updateTheme(systemTheme);
    }
};

export function initializeTheme() {
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
    
    if (savedAppearance) {
        // Use saved preference
        updateTheme(savedAppearance);
    } else {
        // Use system preference as initial default
        const systemTheme = mediaQuery.matches ? 'dark' : 'light';
        updateTheme(systemTheme);
        
        // Set up system theme change listener only if no preference is saved
        mediaQuery.addEventListener('change', handleSystemThemeChange);
    }
}

export function useAppearance() {
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    
    const appearance = ref<Appearance>(savedAppearance || systemTheme);

    onMounted(() => {
        initializeTheme();
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        localStorage.setItem('appearance', value);
        updateTheme(value);
        
        // Remove system theme listener when user makes explicit choice
        mediaQuery.removeEventListener('change', handleSystemThemeChange);
    }

    return {
        appearance,
        updateAppearance,
    };
}
