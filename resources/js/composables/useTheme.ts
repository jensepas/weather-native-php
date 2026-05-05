import { useDark, usePreferredDark } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

type ThemeMode = 'auto' | 'light' | 'dark';

const preferredDark = usePreferredDark();

const mode = ref<ThemeMode>('auto');

const isDark = useDark({
    selector: 'html',
    attribute: 'class',
    valueDark: 'dark',
    valueLight: '',
    storageKey: 'theme',
});

// init côté client uniquement
if (globalThis.window !== undefined) {
    const stored = localStorage.getItem('theme-mode') as ThemeMode | null;

    if (stored) {
        mode.value = stored;
    }
}

// sync logique
watch(
    [mode, preferredDark],
    () => {
        if (mode.value === 'auto') {
            isDark.value = preferredDark.value;
        } else {
            isDark.value = mode.value === 'dark';
        }
    },
    { immediate: true },
);

// persistance (client only)
watch(mode, (val) => {
    if (globalThis.window !== undefined) {
        localStorage.setItem('theme-mode', val);
    }
});

export function useTheme() {
    const theme = computed(() => (isDark.value ? 'dark' : 'light'));

    return {
        theme,
        mode,
        setAuto: () => (mode.value = 'auto'),
        setLight: () => (mode.value = 'light'),
        setDark: () => (mode.value = 'dark'),
    };
}
