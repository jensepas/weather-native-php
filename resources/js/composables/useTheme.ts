import { useDark, useToggle, usePreferredDark } from '@vueuse/core'
import { computed, ref, watch } from 'vue'

const preferredDark = usePreferredDark()
type ThemeMode = 'auto' | 'light' | 'dark';
const mode = ref<ThemeMode>(
    (localStorage.getItem('theme-mode') as ThemeMode) || 'auto'
)

const isDark = useDark({
    selector: 'html',
    attribute: 'class',
    valueDark: 'dark',
    valueLight: '',
    storageKey: 'theme', // optionnel maintenant
})

//sync logique
watch(
    [mode, preferredDark],
    () => {
        if (mode.value === 'auto') {
            isDark.value = preferredDark.value
        } else {
            isDark.value = mode.value === 'dark'
        }
    },
    { immediate: true }
)

// persistance
watch(mode, (val) => {
    localStorage.setItem('theme-mode', val)
})


export function useTheme() {
    const theme = computed(() => (isDark.value ? 'dark' : 'light'))

    return {
        theme,
        mode,

        setAuto: () => (mode.value = 'auto'),
        setLight: () => (mode.value = 'light'),
        setDark: () => (mode.value = 'dark'),
    }
}
