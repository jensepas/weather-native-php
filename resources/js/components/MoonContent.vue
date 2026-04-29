<script lang="ts" setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

import { useSettingsStore } from '@/stores/settingsStore';
import type { WeatherResponse } from '@/types/WeatherResponse';

const props = defineProps<{
    data: WeatherResponse;
    isDay: boolean;
}>();

const settingsStore = useSettingsStore();

// Fonctions de conversion pour la distance (km vers miles)
const convertDistance = (km: number) => {
    if (settingsStore.selectedUnits === 'imperial') {
        return km * 0.621371; // km to miles
    }

    return km;
};

const getDistanceUnit = () => {
    return settingsStore.selectedUnits === 'imperial' ? 'miles' : 'km';
};

/**
 * Temps actuel dans le timezone
 */
function getNowInTimezone(tz?: string) {
    return new Date(
        new Date().toLocaleString('en-US', { timeZone: tz || 'UTC' }),
    );
}

function toDate(v?: string | { date: string }) {
    if (!v) {
        return null;
    }

    const dateStr = typeof v === 'string' ? v : v.date;

    return new Date(dateStr);
}

/**
 * Progression de la journée (0 → 1)
 */

const nowProgress = computed(() => {
    const now =
        toDate(props.data.localDate) ??
        getNowInTimezone(props.data.time.timezone);

    const start = new Date(now);
    start.setHours(0, 0, 0, 0);

    return (now.getTime() - start.getTime()) / 86400000;
});

const moonProgress = computed(() => {
    const now =
        toDate(props.data.localDate) ??
        getNowInTimezone(props.data.time.timezone);
    const start = toDate(props.data.astronomy.moon.moonrise);
    const end = toDate(
        props.data.astronomy.moon.moonset ??
            props.data.astronomy.moon?.moonset_next,
    );
    let progress = 0;

    if (start !== null && end !== null && now !== null) {
        if (end > start) {
            if (now < start) {
                /* empty */
            } else if (now > end) {
                progress = 1;
            } else {
                progress =
                    (now.getTime() - start.getTime()) /
                    (end.getTime() - start.getTime());
            }
        }
    }

    return progress;
});

/**
 * Position sur l’arc
 */
const moonPosition = computed(() => {
    const r = 130;
    const cx = 150;
    const cy = 140;
    const angle = Math.PI * (1 - moonProgress.value);

    return {
        x: cx + r * Math.cos(angle),
        y: cy - r * Math.sin(angle),
    };
});

/**
 * Format heure
 */
function formatHour(date?: string | null) {
    if (!date) {
        return '-';
    }

    try {
        const d = new Date(date);

        if (Number.isNaN(d.getTime())) {
            return '-';
        }

        return d.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return '-';
    }
}

/**
 * Durée entre 2 dates
 */
function durationBetween(start?: string | null, end?: string | null) {
    if (!start || !end) {
        return null;
    }

    try {
        const s = new Date(start).getTime();
        const e = new Date(end).getTime();

        if (Number.isNaN(s) || Number.isNaN(e)) {
            return null;
        }

        let diff = (e - s) / 1000;

        if (diff < 0) {
            diff += 86400;
        }

        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);

        return `${h}h ${m}m`;
    } catch {
        return null;
    }
}

const duration = computed(() =>
    durationBetween(
        props.data.astronomy.moon?.moonrise,
        props.data.astronomy.moon?.moonset ??
            props.data.astronomy.moon?.moonset_next,
    ),
);

// Propriété calculée pour la distance de la lune
const displayMoonDistance = computed(() => {
    const distanceKm = props.data.astronomy.moon?.distance;

    if (typeof distanceKm === 'number') {
        const convertedDistance = convertDistance(distanceKm);

        return convertedDistance.toLocaleString('fr-FR', {
            maximumFractionDigits: 0,
        });
    }

    return '-';
});

function pos(v?: string | { date: string }) {
    const d = toDate(v);

    if (!d) {
        return 0;
    }

    const start = new Date(d);
    start.setHours(0, 0, 0, 0);

    const p = (d.getTime() - start.getTime()) / 86400000;

    return `${p * 100}`;
}

const now = ref(new Date());

let interval: any;

onMounted(() => {
    interval = setInterval(() => {
        now.value = getNowInTimezone(props.data.time.timezone) ?? new Date();
    }, 1000); // update every second
});

onUnmounted(() => clearInterval(interval));

type Segment = {
    start: number;
    end: number;
};

const getMoonSegments = (): Segment[] => {
    const rise = Math.round(Number(pos(props.data.astronomy.moon?.moonrise)));
    const set = Math.round(
        Number(
            pos(
                props.data.astronomy.moon?.moonset ??
                    props.data.astronomy.moon?.moonset_next,
            ),
        ),
    );

    // on filtre les null ici → TS est content
    if (rise == null || set == null) {
        return [];
    }

    // cas normal
    if (rise < set) {
        return [{ start: rise, end: set }];
    }

    // passage minuit
    return [
        { start: 0, end: set },
        { start: rise, end: 100 },
    ];
};
</script>

<template>
    <div
        class="col-span-2 mb-3 items-center gap-3 space-y-3 rounded-2xl bg-white/10 p-3"
    >
        <h3
            class="border-b border-white/5 pb-2 text-xs font-bold tracking-widest uppercase opacity-40"
        >
            Lune
        </h3>

        <!-- Arc + lune -->
        <div class="rounded-2xl bg-white/10 p-2">
            <div class="mx-auto w-full max-w-md">
                <svg class="w-full" viewBox="0 0 300 160">
                    <path
                        d="M 20 140 A 130 130 0 0 1 280 140"
                        fill="none"
                        stroke="#e5e7eb"
                        stroke-width="2"
                    />
                    <!-- Lune -->
                    <foreignObject
                        v-if="moonProgress > 0 && moonProgress < 1"
                        :x="moonPosition.x - 15"
                        :y="moonPosition.y - 15"
                        height="35"
                        width="35"
                    >
                        <div xmlns="http://www.w3.org/1999/xhtml">
                            <i
                                :class="props.data.astronomy.moonDetails?.icon"
                                style="font-size: 30px; color: #f1f1f1"
                            ></i>
                        </div>
                    </foreignObject>
                </svg>

                <!-- Progress bar -->
                <div
                    class="relative mt-4 h-4 overflow-hidden rounded-full bg-indigo-900"
                >
                    <!-- zone visible lune -->
                    <div
                        v-for="(segment, i) in getMoonSegments()"
                        :key="i"
                        class="absolute top-0 bottom-0 bg-indigo-400/40"
                        :style="{
                            left: segment.start + '%',
                            width: segment.end - segment.start + '%',
                        }"
                    />

                    <!-- lever -->
                    <div
                        v-if="pos(props.data.astronomy.moon?.moonrise)"
                        class="absolute top-1/2 -translate-y-1/2"
                        :style="{
                            left:
                                pos(props.data.astronomy.moon?.moonrise) + '%',
                        }"
                    >
                        <i class="wi wi-moonrise text-white-500 text-sm"></i>
                    </div>

                    <!-- coucher -->
                    <div
                        v-if="
                            pos(
                                props.data.astronomy.moon?.moonset ??
                                    props.data.astronomy.moon?.moonset_next,
                            ) + '%'
                        "
                        class="absolute top-1/2 -translate-y-1/2"
                        :style="{
                            left:
                                pos(
                                    props.data.astronomy.moon?.moonset ??
                                        props.data.astronomy.moon?.moonset_next,
                                ) + '%',
                        }"
                    >
                        <i class="wi wi-moonset text-white-500 text-sm"></i>
                    </div>

                    <!-- position actuelle -->
                    <div
                        class="absolute top-1/2 -translate-y-1/2"
                        :style="{ left: `calc(${nowProgress * 100}% - 8px)` }"
                    >
                        <i
                            :class="props.data.astronomy.moonDetails?.icon"
                            class="text-white"
                        ></i>
                    </div>
                </div>
                <!-- Heure -->
                <p class="mt-3 text-center text-xs opacity-60">
                    Temps réel — {{ now.toLocaleTimeString('fr-FR') }}
                </p>
            </div>
        </div>

        <!-- Infos -->
        <div class="grid grid-cols-2 gap-3 text-center">
            <!-- Always up -->
            <div
                v-if="props.data.astronomy.moon?.alwaysUp !== undefined"
                class="col-span-2 rounded-xl bg-white/10 p-2"
            >
                <p class="text-[9px] font-bold uppercase opacity-60">
                    Lever / Coucher
                </p>
                <p class="font-bold">
                    {{
                        props.data.astronomy.moon.alwaysUp
                            ? 'Visible toute la journée'
                            : 'Invisible aujourd’hui'
                    }}
                </p>
            </div>

            <!-- Lever / Coucher -->
            <template v-else>
                <div class="rounded-xl bg-white/10 p-2">
                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Lever
                    </p>
                    <p class="font-bold">
                        <i class="wi wi-moonrise text-sm text-yellow-300"></i>
                        {{ formatHour(props.data.astronomy.moon?.moonrise) }}
                    </p>
                </div>

                <div class="rounded-xl bg-white/10 p-2">
                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Coucher
                    </p>
                    <p class="font-bold">
                        <i class="wi wi-moonset text-sm text-orange-400"></i>
                        {{
                            formatHour(
                                props.data.astronomy.moon?.moonset ??
                                    props.data.astronomy.moon?.moonset_next,
                            )
                        }}
                        <span
                            v-if="
                                !props.data.astronomy.moon?.moonset &&
                                props.data.astronomy.moon?.moonset_next
                            "
                            class="block text-[10px] opacity-50"
                        >
                            (demain)</span
                        >
                    </p>
                </div>
            </template>

            <!-- Phase -->
            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-3">
                <i
                    :class="props.data.astronomy.moonDetails?.icon"
                    class="text-4xl"
                ></i>
                <div class="text-left">
                    <p class="text-xs uppercase opacity-60">Phase</p>
                    <p class="text-sm font-semibold">
                        {{ props.data.astronomy.moon?.label }}
                    </p>
                </div>
            </div>

            <!-- Illumination -->
            <div
                class="flex flex-col justify-center gap-2 rounded-2xl bg-white/10 p-3"
            >
                <div class="flex items-center justify-between">
                    <p class="text-xs opacity-60">Illumination</p>
                    <p class="text-sm font-bold">
                        {{
                            (
                                (props.data.astronomy.moon?.fraction || 0) * 100
                            ).toFixed(1)
                        }}%
                    </p>
                </div>

                <div
                    class="relative h-2 overflow-hidden rounded-full bg-white/10"
                >
                    <div
                        :style="{
                            width:
                                (props.data.astronomy.moon?.fraction || 0) *
                                    100 +
                                '%',
                        }"
                        class="h-full bg-gray-400"
                    />
                </div>
            </div>

            <!-- Durée -->
            <div class="rounded-xl bg-white/10 p-2">
                <p class="text-[9px] font-bold uppercase opacity-60">
                    Durée visible
                </p>
                <p class="font-bold">{{ duration ?? '-' }}</p>
            </div>

            <!-- Distance -->
            <div class="rounded-xl bg-white/10 p-2">
                <p class="text-[9px] font-bold uppercase opacity-60">
                    Distance
                </p>
                <p class="text-sm font-bold">
                    {{ displayMoonDistance }}
                    <span class="text-[10px] font-normal opacity-50">{{
                        getDistanceUnit()
                    }}</span>
                </p>
            </div>
        </div>
    </div>
</template>
