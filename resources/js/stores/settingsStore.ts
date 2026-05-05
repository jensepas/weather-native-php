import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useSettingsStore = defineStore('settings', () => {
    const selectedUnits = ref(localStorage.getItem('units') || 'metric');
    const selectedGPS = ref(localStorage.getItem('units') || 'DD');

    watch(selectedUnits, (newUnits) => {
        localStorage.setItem('units', newUnits);
    });
    watch(selectedGPS, (newUnits) => {
        localStorage.setItem('units', newUnits);
    });

    return {
        selectedUnits,
        selectedGPS,
    };
});
