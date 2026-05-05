<script lang="ts" setup>
import { computed } from 'vue';
import { useSettingsStore } from '@/stores/settingsStore';
import type { WeatherResponse } from '@/types/WeatherResponse';

const props = defineProps<{
    data: WeatherResponse;
}>();

const settingsStore = useSettingsStore();

const convertTemperature = (tempC: number) => {
    if (settingsStore.selectedUnits === 'imperial') {
        return (tempC * 9) / 5 + 32; // Celsius to Fahrenheit
    }

    return tempC;
};

const getTemperatureUnit = () => {
    return settingsStore.selectedUnits === 'imperial' ? '°F' : '°C';
};

/**
 * Skip le jour courant (équivalent ->skip(1))
 */
const days = computed(() => props.data.weather.forecast);

/**
 * Couleur température (équivalent tempColor PHP)
 */
function tempColor(temp: number | null) {
    if (temp === null) {
        return '';
    }

    // Utilisez la température en Celsius pour la logique de couleur
    const tempC =
        settingsStore.selectedUnits === 'imperial'
            ? ((temp - 32) * 5) / 9
            : temp;

    if (tempC <= 0) {
        return 'text-blue-400';
    }

    if (tempC <= 10) {
        return 'text-cyan-400';
    }

    if (tempC <= 20) {
        return 'text-green-400';
    }

    if (tempC <= 30) {
        return 'text-yellow-400';
    }

    return 'text-red-400';
}

// Fonctions pour l'affichage des températures converties
const displayMaxMinTemperature = (tempC: number | null) => {
    if (tempC === null) {
        return '-';
    }

    const temp = convertTemperature(tempC);

    return `${temp.toFixed(1)}${getTemperatureUnit()}`;
};

</script>

<template>
    <div class="col-span-2 space-y-3 rounded-2xl bg-white/10 p-3">
        <h3
            class="border-b border-white/5 pb-2 text-xs font-bold tracking-widest uppercase opacity-40"
        >
            Prévisions (14j)
        </h3>

        <div
            v-for="day in days"
            :key="day.date"
            class="flex items-center justify-between rounded-2xl bg-white/10 px-3 py-2"
        >
            <!-- Jour -->
            <div class="w-16 text-xs opacity-70">
                {{ day.date }}
            </div>

            <!-- Icône + pluie -->
            <div class="flex flex-1 items-center justify-center gap-2">
                <i :class="[day.icon.icon, 'text-2xl drop-shadow']"></i>

                <template v-if="Number(day.rain_prob) > 0">
                    <div
                        class="mt-1 h-1 w-full overflow-hidden rounded-full bg-white/10"
                    >
                        <div
                            :style="{ width: day.rain_prob + '%' }"
                            class="h-full bg-blue-600"
                        ></div>
                    </div>

                    <span class="text-[10px] font-bold text-blue-300">
                        {{ day.rain_prob }}%
                    </span>
                </template>

                <template v-else>
                    <div
                        class="mt-1 h-1 w-full overflow-hidden rounded-full"
                    ></div>
                </template>
            </div>

            <!-- Températures -->
            <div class="w-20 text-center">
                <p :class="tempColor(day.max)" class="text-sm font-bold">
                    {{ displayMaxMinTemperature(day.max) }}
                </p>
                <p :class="tempColor(day.min)" class="text-xs opacity-60">
                    {{ displayMaxMinTemperature(day.min) }}
                </p>
            </div>

            <!-- Soleil -->
            <div class="w-20 space-y-0.5 text-right text-xs">
                <div class="flex items-center justify-center gap-1">
                    <i class="wi wi-sunrise text-sm text-yellow-300"></i>
                    <span>{{ day.sunrise ?? '-' }}</span>
                </div>

                <div class="flex items-center justify-center gap-1 opacity-70">
                    <i class="wi wi-sunset text-sm text-orange-400"></i>
                    <span>{{ day.sunset ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
