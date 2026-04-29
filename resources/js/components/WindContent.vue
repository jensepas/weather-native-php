<script lang="ts" setup>
import { computed } from 'vue';
import { useSettingsStore } from '@/stores/settingsStore';
import type { WeatherResponse } from '@/types/WeatherResponse';

const props = defineProps<{
    data: WeatherResponse;
}>();

const settingsStore = useSettingsStore();

const convertWindSpeed = (speedKmH: number) => {
    if (settingsStore.selectedUnits === 'imperial') {
        return speedKmH * 0.621371; // km/h to mph
    }

    return speedKmH;
};

const getWindSpeedUnit = () => {
    return settingsStore.selectedUnits === 'imperial' ? 'mph' : 'km/h';
};

function getBeaufortScale(speedKmH: number) { // speed is always in km/h for this function's logic
    if (speedKmH < 1) {
        return {
            level: 0,
            label: 'Calme',
            terre: 'La fumée monte verticalement',
            mer: 'La mer est comme un miroir.',
        };
    }

    if (speedKmH < 6) {
        return {
            level: 1,
            label: 'Très légère brise',
            terre: 'Fumée déviée, feuilles immobiles',
            mer: 'Quelques rides.',
        };
    }

    if (speedKmH < 12) {
        return {
            level: 2,
            label: 'Légère brise',
            terre: 'On sent le vent sur le visage, les feuilles bougent.',
            mer: 'Vaguelettes ne déferlant pas.',
        };
    }

    if (speedKmH < 20) {
        return {
            level: 3,
            label: 'Petite brise',
            terre: 'Les drapeaux flottent bien. Les feuilles sont sans cesse en mouvement.',
            mer: 'Très petites vagues. Les moutons apparaissent.',
        };
    }

    if (speedKmH < 29) {
        return {
            level: 4,
            label: 'Jolie brise',
            terre: 'Les poussières s’envolent, les petites branches plient.',
            mer: 'Petites vagues, nombreux moutons.',
        };
    }

    if (speedKmH < 39) {
        return {
            level: 5,
            label: 'Bonne brise',
            terre: 'Les petits arbres balancent. Les sommets de tous les arbres sont agités.',
            mer: 'Vagues modérées, moutons, éventuellement embruns.',
        };
    }

    if (speedKmH < 50) {
        return {
            level: 6,
            label: 'Vent frais',
            terre: 'Les grandes branches sont agitées. On entend siffler le vent.',
            mer: 'Crête d’écume blanche, lame, embruns.',
        };
    }

    if (speedKmH < 62) {
        return {
            level: 7,
            label: 'Grand frais',
            terre: 'Tous les arbres s’agitent. Efforts pour marcher contre le vent.',
            mer: 'Trainées d’écume, lames déferlantes.',
        };
    }

    if (speedKmH < 75) {
        return {
            level: 8,
            label: 'Coup de vent',
            terre: 'Quelques branches cassent. La marche contre le vent est difficile.',
            mer: 'Tourbillons d’écumes à la crête des lames, trainées d’écume.',
        };
    }

    if (speedKmH < 89) {
        return {
            level: 9,
            label: 'Vent frais',
            terre: 'Le vent peut endommager les bâtiments.',
            mer: 'Grosses lames déferlantes, visibilité réduite par les embruns.',
        };
    }

    if (speedKmH < 103) {
        return {
            level: 10,
            label: 'Tempête',
            terre: 'Rare sur les terres. Gros dégâts aux habitations. Arbres déracinés.',
            mer: 'Très grosses lames à longue crête en panache. Surface des eaux blanche. Visibilité réduite.',
        };
    }

    if (speedKmH < 118) {
        return {
            level: 11,
            label: 'Violente tempête',
            terre: 'Très rare sur les terres. Très gros dégâts.',
            mer: 'Lames exceptionnellement hautes. Mer recouverte de bancs d’écume blanche. Visibilité réduite.',
        };
    }

    return {
        level: 12,
        label: 'Ouragan',
        terre: 'Très rare sur les terres. Dégâts très importants.',
        mer: 'Air plein d’écume et d’embruns. Mer entièrement blanche. Visibilité fortement réduite.',
    };
}

const wind = computed(() =>
    getBeaufortScale(props.data.weather.current.wind_speed_10m),
);

const gust = computed(() =>
    getBeaufortScale(props.data.weather.current.wind_gusts_10m),
);

// Propriétés calculées pour les valeurs affichées
const displayWindSpeed = computed(() => {
    const speed = convertWindSpeed(props.data.weather.current.wind_speed_10m);

    return `${speed.toFixed(1)}`;
});

const displayWindGusts = computed(() => {
    const speed = convertWindSpeed(props.data.weather.current.wind_gusts_10m);

    return `${speed.toFixed(1)}`;
});

/**
 * Azimut normalisé
 */
const azimuth = computed(() => {
    const a = props.data.weather.current.wind_direction_10m ?? 0;

    return ((a + 360) % 360).toFixed(1);
});

const rotation = computed(() => {
    return `rotate(${azimuth.value})`;
});
</script>

<template>
    <div
        class="col-span-2 mb-3 items-center gap-3 space-y-3 rounded-2xl bg-white/10 p-3"
    >
        <h3
            class="border-b border-white/5 pb-2 text-xs font-bold tracking-widest uppercase opacity-40"
        >
            Vent
        </h3>

        <div class="grid grid-cols-2 gap-3">
            <!-- Boussole -->
            <div
                class="flex flex-col items-center justify-center rounded-2xl bg-white/10 p-3 text-center"
            >
                <svg
                    class="wind-compass"
                    height="80"
                    viewBox="0 0 100 100"
                    width="80"
                >
                    <circle
                        cx="50"
                        cy="50"
                        fill="none"
                        opacity="0.2"
                        r="45"
                        stroke="currentColor"
                        stroke-width="2"
                    />

                    <line
                        opacity="0.3"
                        stroke="currentColor"
                        stroke-width="1"
                        x1="50"
                        x2="50"
                        y1="10"
                        y2="90"
                    />
                    <line
                        opacity="0.3"
                        stroke="currentColor"
                        stroke-width="1"
                        x1="10"
                        x2="90"
                        y1="50"
                        y2="50"
                    />

                    <text
                        font-size="10"
                        stroke="currentColor"
                        text-anchor="middle"
                        x="50"
                        y="18"
                    >
                        N
                    </text>
                    <text
                        font-size="10"
                        stroke="currentColor"
                        text-anchor="middle"
                        x="82"
                        y="54"
                    >
                        E
                    </text>
                    <text
                        font-size="10"
                        stroke="currentColor"
                        text-anchor="middle"
                        x="50"
                        y="92"
                    >
                        S
                    </text>
                    <text
                        font-size="10"
                        stroke="currentColor"
                        text-anchor="middle"
                        x="18"
                        y="54"
                    >
                        W
                    </text>

                    <!-- rotation dynamique -->
                    <g
                        :transform="rotation"
                        class="wind-arrow"
                        style="transition: transform 0.6s ease"
                    >
                        <path d="M50 20 L58 40 H52 V75 H48 V40 H42 Z" />
                    </g>
                </svg>
            </div>

            <!-- Infos vent -->
            <div class="rounded-2xl bg-white/10 p-3">
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Direction
                    </p>
                    <p class="wind-direction text-right font-semibold">
                        {{ props.data.weather.current.wind_direction_10m }}
                        <span class="text-[10px]">°</span>
                    </p>

                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Vent
                    </p>
                    <p class="text-right font-semibold">
                        {{ displayWindSpeed }}
                        <span class="text-[10px]">{{ getWindSpeedUnit() }}</span>
                    </p>

                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Rafale
                    </p>
                    <p class="text-right font-semibold">
                        {{ displayWindGusts }}
                        <span class="text-[10px]">{{ getWindSpeedUnit() }}</span>
                    </p>
                </div>
            </div>
            <div class="col-span-2 gap-3">
                <!-- Vent -->
                <div
                    class="mb-3 flex flex-col gap-3 rounded-2xl bg-white/10 p-4"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase opacity-60">
                            Vent
                        </p>

                        <i
                            :class="`wi wi-wind-beaufort-${wind.level} text-lg`"
                        ></i>
                    </div>

                    <div>
                        <p class="text-sm font-bold">{{ wind.label }}</p>
                        <p class="text-[11px] opacity-70">
                            <i class="fa-solid fa-mountain-city"></i>
                            {{ wind.terre }}
                        </p>
                        <p class="text-[11px] opacity-70">
                            <i class="fa-solid fa-water"></i> {{ wind.mer }}
                        </p>
                    </div>

                    <!-- Barre -->
                    <div class="h-1.5 overflow-hidden rounded-full bg-white/10">
                        <div
                            :style="{ width: (wind.level / 12) * 100 + '%' }"
                            class="h-full bg-gradient-to-r from-green-400 via-yellow-400 to-red-500"
                        ></div>
                    </div>
                </div>

                <!-- Rafales -->
                <div class="flex flex-col gap-3 rounded-2xl bg-white/10 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase opacity-60">
                            Rafales
                        </p>

                        <i
                            :class="`wi wi-wind-beaufort-${gust.level} text-lg`"
                        ></i>
                    </div>

                    <div>
                        <p class="text-sm font-bold">{{ gust.label }}</p>
                        <p class="text-[11px] opacity-70">
                            <i class="fa-solid fa-mountain-city"></i>
                            {{ gust.terre }}
                        </p>
                        <p class="text-[11px] opacity-70">
                            <i class="fa-solid fa-water"></i> {{ gust.mer }}
                        </p>
                    </div>

                    <div class="h-1.5 overflow-hidden rounded-full bg-white/10">
                        <div
                            :style="{ width: (gust.level / 12) * 100 + '%' }"
                            class="h-full bg-gradient-to-r from-green-400 via-yellow-400 to-red-500"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
