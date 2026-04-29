import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useSettingsStore = defineStore('settings', () => {
    const selectedUnits = ref(localStorage.getItem('units') || 'metric');

    watch(selectedUnits, (newUnits) => {
        localStorage.setItem('units', newUnits);
    });

    return {
        selectedUnits,
    };
});
