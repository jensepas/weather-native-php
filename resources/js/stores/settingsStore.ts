import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useSettingsStore = defineStore('settings', () => {
    const selectedUnits = ref(localStorage.getItem('units') || 'metric');
    const selectedGPS = ref(localStorage.getItem('local') || 'DD');
    // Convertir les chaînes 'true'/'false' en booléens
    const selectedWind = ref(localStorage.getItem('wind') === 'true');
    const selectedForecast = ref(localStorage.getItem('forecast') === 'true');
    const selectedSun = ref(localStorage.getItem('sun') === 'true');
    const selectedMoon = ref(localStorage.getItem('moon') === 'true');

    watch(selectedUnits, (newUnits) => {
        localStorage.setItem('units', newUnits);
    });
    watch(selectedGPS, (newUnits) => {
        localStorage.setItem('local', newUnits);
    });
    // Convertir les booléens en chaînes 'true'/'false' avant de sauvegarder
    watch(selectedWind, (newVal) => {
        localStorage.setItem('wind', newVal.toString());
    });
    watch(selectedForecast, (newVal) => {
        localStorage.setItem('forecast', newVal.toString());
    });
    watch(selectedSun, (newVal) => {
        localStorage.setItem('sun', newVal.toString());
    });
    watch(selectedMoon, (newVal) => {
        localStorage.setItem('moon', newVal.toString());
    });

    return {
        selectedUnits,
        selectedGPS,
        selectedWind,
        selectedForecast,
        selectedSun,
        selectedMoon,
    };
});
export class SettingsStore {
    selectedUnits: string;
    selectedGPS: string;
    selectedWind: boolean;
    selectedForecast: boolean;
    selectedSun: boolean;
    selectedMoon: boolean;
    constructor(
        selectedUnits: string,
        selectedGPS: string,
        selectedWind: boolean,
        selectedForecast: boolean,
        selectedSun: boolean,
        selectedMoon: boolean,
    ) {
        this.selectedUnits = selectedUnits;
        this.selectedGPS = selectedGPS;
        this.selectedWind = selectedWind;
        this.selectedForecast = selectedForecast;
        this.selectedSun = selectedSun;
        this.selectedMoon = selectedMoon;
    }
}
