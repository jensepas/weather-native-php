<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue';
import { getUvData } from '@/composables/getUvData';
import { getWeatherDescription } from '@/composables/getWeatherDescription';
import { getWindIcon } from '@/composables/getWindIcon';
import type { WeatherResponse } from '@/types/WeatherResponse';

const props = defineProps<{
    data: WeatherResponse;
}>();

// heure courante
const currentHour = computed(() => {
    return Number.parseInt(props.data.time.localTime.split(':')[0]);
});

// 🎨 helpers (remplace PHP)
const tempColor = (temp: number) => {
    if (temp <= 0) {
        return 'text-blue-400';
    }

    if (temp <= 10) {
        return 'text-cyan-300';
    }

    if (temp <= 20) {
        return 'text-green-300';
    }

    if (temp <= 30) {
        return 'text-yellow-300';
    }

    return 'text-red-400';
};

// UV
const uv = computed(() => {
    return (
        props.data.weather.hourly?.uv_index?.[currentHour.value] ??
        props.data.weather.todayDetails.uv
    );
});

const uvPercent = computed(() => Math.min((uv.value / 11) * 100, 100));
const maxUvPercent = computed(() =>
    Math.min((props.data.weather.todayDetails.uv / 11) * 100, 100),
);

const uvData = computed(() => getUvData(uv.value));

onMounted(() => {
    (function () {
        const theme = localStorage.getItem('theme');
        const prefersDark = globalThis.matchMedia(
            '(prefers-color-scheme: dark)',
        ).matches;

        if (theme === 'dark' || (!theme && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
});
</script>

<template>
    <div class="col-span-2 items-center space-y-3 rounded-2xl bg-white/10 p-3">
        <div
            :class="[
                props.data.weather?.theme?.bg,
                props.data.weather?.theme?.text,
            ]"
            class="rounded-2xl"
        >
            <div class="mb-4 text-center">
                <div class="relative flex items-center justify-center">
                    <h1 class="text-center text-2xl font-bold">
                        {{ props.data.selectedCityInfos.name }}
                    </h1>
                </div>

                {{ props.data.selectedCityInfos.admin1 }},
                {{ props.data.selectedCityInfos.country }}
                <p class="text-xs tracking-wide opacity-60">
                    {{ props.data.time.localDate }} •
                    <span class="text-sm font-semibold">{{
                        props.data.time.localTime
                    }}</span>
                </p>

                <div
                    class="mt-1 flex items-center justify-center gap-2 text-[10px] opacity-40"
                >
                    <span
                        >{{ props.data.location.latitude }},
                        {{ props.data.location.longitude }} -
                        {{ props.data.selectedCityInfos.elevation }}m -
                        {{
                            props.data.selectedCityInfos.population?.toLocaleString(
                                'fr-FR',
                            ) || '0'
                        }}
                        hab</span
                    >
                    <span
                        v-if="props.data.time.cachedAt"
                        class="flex items-center gap-1"
                    >
                        • <i class="fas fa-history text-[8px]"></i> MAJ :
                        {{ props.data.time.cachedAt }}
                    </span>
                </div>
            </div>

            <!-- Temp principale -->
            <div class="mb-6 flex items-center justify-center gap-6">
                <i
                    :class="
                        props.data.weather.description.icon +
                        ' text-6xl drop-shadow-2xl'
                    "
                />

                <div class="text-center">
                    <p class="text-5xl leading-none font-bold">
                        {{ props.data.weather.current.temperature_2m }}°
                    </p>
                    <p class="mt-1 text-sm opacity-70">
                        {{ props.data.weather.description.desc }}
                    </p>
                </div>
            </div>
            <!-- Min / Max / Ressenti -->
            <div class="grid grid-cols-3 gap-2 p-3 text-center">
                <div
                    :class="tempColor(props.data.weather.todayDetails.min)"
                    class="rounded-xl bg-white/10 p-2"
                >
                    <p class="text-[9px] font-bold uppercase opacity-60">Min</p>
                    <p class="font-semibold">
                        {{ props.data.weather.todayDetails.min }}°
                    </p>
                </div>

                <div
                    :class="tempColor(props.data.weather.todayDetails.max)"
                    class="rounded-xl bg-white/10 p-2"
                >
                    <p class="text-[9px] font-bold uppercase opacity-60">Max</p>
                    <p class="font-semibold">
                        {{ props.data.weather.todayDetails.max }}°
                    </p>
                </div>

                <div
                    :class="
                        tempColor(
                            props.data.weather.current.apparent_temperature,
                        )
                    "
                    class="rounded-xl bg-white/10 p-2"
                >
                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Ressenti
                    </p>
                    <p class="font-semibold">
                        {{ props.data.weather.current.apparent_temperature }}°
                    </p>
                </div>
            </div>
            <!-- Infos secondaires -->
            <div class="grid grid-cols-2 gap-2 p-3 text-center">
                <div class="flex items-center gap-2 rounded-xl bg-white/10 p-2">
                    <i class="wi wi-raindrop text-blue-300"></i>
                    <div>
                        <p class="text-[9px] font-bold uppercase opacity-60">
                            Pluie
                        </p>
                        <p class="font-semibold">
                            {{ props.data.weather.current.precipitation }}
                            mm
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-xl bg-white/10 p-2">
                    <i class="wi wi-umbrella text-blue-400"></i>
                    <div>
                        <p class="text-[9px] font-bold uppercase opacity-60">
                            Probabilité
                        </p>
                        <p class="font-semibold">
                            {{
                                props.data.weather.todayDetails.rain_prob ??
                                '-'
                            }}%
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-xl bg-white/10 p-2">
                    <i class="wi wi-humidity text-cyan-300"></i>
                    <div>
                        <p class="text-[9px] font-bold uppercase opacity-60">
                            Humidité
                        </p>
                        <p class="font-semibold">
                            {{
                                props.data.weather.current.relative_humidity_2m
                            }}%
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-xl bg-white/10 p-2">
                    <i class="wi wi-barometer text-gray-300"></i>
                    <div>
                        <p class="text-[9px] font-bold uppercase opacity-60">
                            Pression
                        </p>
                        <p class="font-semibold">
                            {{ props.data.weather.current.surface_pressure }}
                            hPa
                        </p>
                    </div>
                </div>
            </div>
            <!-- UV -->
            <div class="mx-3 gap-2 rounded-2xl bg-white/10 p-3 text-center">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="wi wi-day-sunny text-lg text-yellow-400"></i>
                        <p class="text-[10px] font-bold uppercase opacity-60">
                            Indice UV
                        </p>
                    </div>

                    <p :class="uvData.text" class="text-sm font-bold">
                        {{ uv }} - {{ uvData.label }}
                    </p>
                </div>
                <!-- Barre -->
                <div
                    class="relative h-2 overflow-hidden rounded-full bg-gradient-to-r from-green-400 via-orange-400 via-red-500 via-yellow-400 to-purple-600"
                >
                    <div
                        :style="{ left: `calc(${maxUvPercent}% - 4px)` }"
                        class="absolute h-2 w-2 rounded bg-red-600"
                    ></div>
                    <div
                        :style="{ left: `calc(${uvPercent}% - 4px)` }"
                        class="absolute h-2 w-2 rounded bg-white"
                    ></div>
                </div>
                <p class="text-xs opacity-60">
                    {{ uvData.description }}
                </p>
            </div>
            <span
                v-if="props.data.time.cachedAt"
                class="mx-5 flex items-center justify-end gap-1 text-center text-[8px] opacity-60"
            >
                <i class="fas fa-history"></i> MAJ :
                {{ props.data.time.cachedAt }}
            </span>
        </div>

        <!-- Hourly -->
        <div
            class="flex items-center py-2.5 text-xs font-bold tracking-widest uppercase"
        >
            <i class="far fa-clock mr-2 opacity-70"></i>
            Prévisions heure par heure
        </div>

        <div
            id="hourlyContainer"
            class="relative max-h-60 space-y-2 overflow-y-auto"
        >
            <div
                v-for="i in 24"
                :id="`hour-${i - 1}`"
                :key="i"
                :class="
                    i - 1 === currentHour
                        ? 'active-hour bg-white/40 font-bold opacity-100'
                        : 'bg-white/10 opacity-80'
                "
                class="flex items-center justify-between rounded-xl px-3 py-2 transition"
            >
                <div class="w-14 text-sm">
                    {{ String(i - 1).padStart(2, '0') }}h
                </div>

                <div class="w-10 text-center">
                    <i
                        :class="
                            getWeatherDescription(
                                props.data.weather.hourly.weather_code[i - 1],
                            ).icon
                        "
                    />
                </div>

                <div
                    :class="
                        tempColor(
                            props.data.weather.hourly.temperature_2m[i - 1],
                        )
                    "
                    class="flex-1 text-right text-sm font-bold"
                >
                    {{ props.data.weather.hourly.temperature_2m[i - 1] }}°
                </div>

                <div class="flex-1 text-right text-xs opacity-70">
                    <i class="wi wi-raindrop text-blue-300"></i>
                    {{ props.data.weather.hourly.precipitation[i - 1] }}
                    <span class="text-[8px]">mm</span>
                </div>

                <div class="flex-1 text-right text-xs opacity-70">
                    <i
                        :class="
                            getWindIcon(
                                props.data.weather.hourly.wind_direction_10m[
                                    i - 1
                                ],
                            )
                        "
                    />
                    {{ props.data.weather.hourly.wind_speed_10m[i - 1] }}
                    <span class="text-[8px]">km/h</span>
                </div>
            </div>
        </div>
    </div>
</template>
