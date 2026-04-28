import { useDark, useToggle } from '@vueuse/core';
import { computed } from 'vue';

// useDark gère automatiquement :
// 1. La classe 'dark' sur l'élément html
// 2. La synchronisation avec localStorage (clé 'theme')
// 3. La détection de 'prefers-color-scheme'
const isDark = useDark({
    selector: 'html',
    attribute: 'class',
    valueDark: 'dark',
    valueLight: '',
    storageKey: 'theme',
});

const toggleDark = useToggle(isDark);

export function useTheme() {
    // On retourne 'dark' ou 'light' pour la compatibilité avec votre code actuel
    // en s'assurant que 'theme' est bien réactif.
    const theme = computed(() => (isDark.value ? 'dark' : 'light'));

    return {
        theme,
        toggleTheme: () => toggleDark(),
    };
}
