<script lang="ts" setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import type { WeatherResponse } from '@/types/WeatherResponse';

interface SunData {
    sunrise?: string | { date: string };
    sunset?: string | { date: string };
    solarNoon?: string | { date: string };
    nightEnd?: string | { date: string };
    nauticalDawn?: string | { date: string };
    dawn?: string | { date: string };
    goldenHourEnd?: string | { date: string };
    goldenHour?: string | { date: string };
    dusk?: string | { date: string };
    nauticalDusk?: string | { date: string };
    night?: string | { date: string };
    localDate: string | { date: string };
}

interface SunFormatter {
    azimuth_deg?: number;
    altitude?: number;
    latitude?: number;
    longitude?: number;
}

interface TodayDetails {
    uv?: number;
}

const props = defineProps<{
    sunData: SunData;
    data: WeatherResponse;
    sunFormatter: SunFormatter;
    todayDetails: TodayDetails;
    isDay: boolean;
    localDate: string | { date: string };
}>();

/* -------------------------
   Utils
------------------------- */

function toDate(v?: string | { date: string }) {
    if (!v) {
        return null;
    }

    const dateStr = typeof v === 'string' ? v : v.date;

    return new Date(dateStr);
}

function formatHour(v?: string | { date: string }) {
    const date = toDate(v);

    if (!date || Number.isNaN(date.getTime())) {
        return '-';
    }

    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getDayProgress(date?: string | { date: string }) {
    const d = toDate(date);

    if (!d) {
        return 0;
    }

    const start = new Date(d);
    start.setHours(0, 0, 0, 0);

    return (d.getTime() - start.getTime()) / 86400000;
}

function durationBetween(
    start?: string | { date: string },
    end?: string | { date: string },
) {
    const sDate = toDate(start);
    const eDate = toDate(end);

    if (!sDate || !eDate) {
        return null;
    }

    const s = sDate.getTime();
    const e = eDate.getTime();

    let diff = (e - s) / 1000;

    if (diff < 0) {
        diff += 86400;
    }

    const h = Math.floor(diff / 3600);
    const m = Math.floor((diff % 3600) / 60);

    return `${h}h ${m}m`;
}

/* -------------------------
   Progression soleil
------------------------- */
function pos(v?: string | { date: string }) {
    const p = getDayProgress(v);

    return `${p * 100}%`;
}

const localDate = computed(() => props.localDate);

const progress = computed(() => {
    const now = toDate(props.localDate);
    const start = toDate(props.sunData.sunrise);
    const end = toDate(props.sunData.sunset);
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

/* -------------------------
   Is day
------------------------- */

const isDay = computed(() => props.isDay);

/* -------------------------
   Position soleil (arc)
------------------------- */

const sunPosition = computed(() => {
    const r = 130;
    const angle = Math.PI * progress.value;

    return {
        x: 150 - Math.cos(angle) * r,
        y: 140 - Math.sin(angle) * r,
    };
});

/* -------------------------
   UV
------------------------- */

function getUvData(uv: number) {
    if (uv <= 2) {
        return { label: 'Faible', text: 'text-green-400' };
    }

    if (uv <= 5) {
        return { label: 'Modéré', text: 'text-yellow-400' };
    }

    if (uv <= 7) {
        return { label: 'Élevé', text: 'text-orange-400' };
    }

    if (uv <= 10) {
        return { label: 'Très élevé', text: 'text-red-500' };
    }

    return { label: 'Extrême', text: 'text-purple-600' };
}

const uv = computed(() => props.todayDetails.uv ?? 0);
const uvData = computed(() => getUvData(uv.value));
const uvPercent = computed(() => Math.min((uv.value / 11) * 100, 100));

const now = ref(new Date());

let interval: any;

function getNowInTimezone(tz?: string) {
    return new Date(
        new Date().toLocaleString('en-US', { timeZone: tz || 'UTC' }),
    );
}

onMounted(() => {
    interval = setInterval(() => {
        now.value = getNowInTimezone(props.data.time.timezone) ?? new Date();
    }, 1000); // update every second
});

onUnmounted(() => clearInterval(interval));

/* -------------------------
   Rows (timeline)
------------------------- */

function row(
    label: string,
    start?: string | { date: string },
    end?: string | { date: string },
    txt = '',
    cls = '',
) {
    return {
        label,
        start,
        end,
        class: cls + txt,
        duration: start && end ? durationBetween(start, end) : null,
    };
}

const rows = computed(() => [
    row(
        'Aube astronomique',
        props.sunData.nightEnd,
        props.sunData.nauticalDawn,
        ' opacity-60',
        'bg-gradient-to-r from-blue-400/20 to-yellow-400/30',
    ),
    row(
        'Aube nautique',
        props.sunData.nauticalDawn,
        props.sunData.dawn,
        ' opacity-60',
        'bg-gradient-to-r from-blue-400/20 to-yellow-400/30',
    ),
    row(
        'Aube civile',
        props.sunData.dawn,
        props.sunData.sunrise,
        ' opacity-60',
        'bg-gradient-to-r from-blue-400/20 to-yellow-400/30',
    ),
    row(
        'Lever',
        props.sunData.sunrise,
        '',
        '',
        'bg-gradient-to-r from-yellow-400/30 to-yellow-400/50',
    ),
    row(
        'Heure dorée',
        props.sunData.sunrise,
        props.sunData.goldenHourEnd,
        ' opacity-60',
        'bg-gradient-to-r from-yellow-300/50 to-yellow-400/80',
    ),
    row(
        'Zénith',
        props.sunData.solarNoon,
        '',
        '',
        'bg-gradient-to-r from-yellow-400/80 to-yellow-400/80',
    ),
    row(
        'Heure dorée',
        props.sunData.goldenHour,
        props.sunData.sunset,
        ' opacity-60',
        'bg-gradient-to-r from-yellow-400/80 to-yellow-300/40',
    ),
    row(
        'Coucher',
        props.sunData.sunset,
        '',
        '',
        'bg-gradient-to-r from-yellow-400/70 to-yellow-400/30',
    ),
    row(
        'Crépuscule civil',
        props.sunData.sunset,
        props.sunData.dusk,
        ' opacity-60',
        'bg-gradient-to-r from-orange-400/20 to-yellow-400/20',
    ),
    row(
        'Crépuscule nautique',
        props.sunData.dusk,
        props.sunData.nauticalDusk,
        ' opacity-60',
        'bg-gradient-to-r from-orange-400/20 to-yellow-400/20',
    ),
    row(
        'Crépuscule astronomique',
        props.sunData.nauticalDusk,
        props.sunData.night,
        ' opacity-60',
        'bg-gradient-to-r from-orange-400/20 to-yellow-400/20',
    ),
]);

function formatRange(
    start?: string | { date: string },
    end?: string | { date: string },
) {
    const s = toDate(start);
    const e = toDate(end);

    if (!s && !e) {
        return '—';
    }

    if (s && e) {
        return `${formatHour(start)} → ${formatHour(end)}`;
    }

    if (!s && e) {
        return `Jusqu’à ${formatHour(end)}`;
    }

    if (s && !e) {
        return `Dès ${formatHour(start)}`;
    }

    return '—';
}

/**
 * Couleur dynamique (jour / nuit)
 */
const color = computed(() => {
    return (props.sunFormatter.altitude ?? 0) > 0 ? '#facc15' : '#9ca3af';
});

/**
 * Azimut normalisé
 */
const azimuth = computed(() => {
    const a = props.sunFormatter.azimuth_deg ?? 0;

    return ((a + 360) % 360).toFixed(0);
});

/**
 * Rotation flèche
 */
const rotation = computed(() => {
    return `rotate(${azimuth.value})`;
});

const segments = computed(() => {
    return [
        {
            start: getDayProgress(props.sunData.nightEnd),
            end: getDayProgress(props.sunData.nauticalDawn),
            color: 'from-indigo-900 to-blue-500',
        },
        {
            start: getDayProgress(props.sunData.nauticalDawn),
            end: getDayProgress(props.sunData.sunrise),
            color: 'from-blue-500 to-yellow-400',
        },
        {
            start: getDayProgress(props.sunData.sunrise),
            end: getDayProgress(props.sunData.sunset),
            color: 'from-yellow-400 to-orange-400',
        },
        {
            start: getDayProgress(props.sunData.sunset),
            end: getDayProgress(props.sunData.night),
            color: 'from-orange-400 to-indigo-900',
        },
    ]
        .filter((seg) => seg.start != null && seg.end != null)
        .map((seg) => {
            const start = seg.start as number;
            let end = seg.end as number;

            // gestion passage minuit
            if (end < start) {
                end += 1;
            }

            return { ...seg, start, end };
        });
});
</script>

<template>
    <div class="col-span-2 mb-3 space-y-3 rounded-2xl bg-white/10 p-3">
        <h3
            class="border-b border-white/5 pb-2 text-xs font-bold uppercase opacity-40"
        >
            Soleil
        </h3>

        <!-- ARC -->
        <div class="rounded-2xl bg-white/10 p-2">
            <svg class="w-full" viewBox="0 0 300 160">
                <path
                    d="M 20 140 A 130 130 0 0 1 280 140"
                    fill="none"
                    stroke="#e5e7eb"
                    stroke-width="2"
                />

                <foreignObject
                    v-if="isDay"
                    :x="sunPosition.x - 16"
                    :y="sunPosition.y - 16"
                    height="35"
                    width="35"
                >
                    <div xmlns="http://www.w3.org/1999/xhtml">
                        <i
                            class="wi wi-day-sunny"
                            style="font-size: 30px; color: #ffd700"
                        ></i>
                    </div>
                </foreignObject>
            </svg>
            <!-- Progress bar -->
            <div
                class="relative mt-4 h-4 overflow-hidden rounded-full bg-indigo-900"
            >
                <!-- Segments -->
                <div
                    v-for="(seg, i) in segments"
                    :key="i"
                    class="absolute top-0 h-full"
                    :style="{
                        left: seg.start * 100 + '%',
                        width: (seg.end - seg.start) * 100 + '%',
                    }"
                >
                    <div
                        class="h-full w-full bg-gradient-to-r"
                        :class="seg.color"
                    ></div>
                </div>

                <!-- Lever -->
                <div
                    v-if="pos(sunData.sunrise)"
                    class="absolute top-1/2 -translate-y-1/2"
                    :style="{ left: pos(sunData.sunrise) }"
                >
                    <i class="wi wi-sunrise text-sm text-yellow-900"></i>
                </div>

                <!-- Zénith -->
                <div
                    v-if="pos(sunData.solarNoon)"
                    class="absolute top-1/2 -translate-y-1/2"
                    :style="{ left: pos(sunData.solarNoon) }"
                >
                    <i class="wi wi-day-sunny text-sm text-white"></i>
                </div>

                <!-- Coucher -->
                <div
                    v-if="pos(sunData.sunset)"
                    class="absolute top-1/2 -translate-y-1/2"
                    :style="{ left: pos(sunData.sunset) }"
                >
                    <i class="wi wi-sunset text-sm text-orange-900"></i>
                </div>

                <!-- Curseur temps réel -->
                <div
                    class="absolute top-0 h-full w-0.5 bg-white"
                    :style="{ left: getDayProgress(localDate) * 100 + '%' }"
                ></div>
            </div>
            <!-- Heure -->
            <p class="mt-3 text-center text-xs opacity-60">
                Temps réel — {{ now.toLocaleTimeString('fr-FR') }}
            </p>
        </div>

        <!-- Lever / Zenit / Coucher -->
        <div class="mb-4 grid grid-cols-3 gap-2 text-center text-sm">
            <div class="rounded-2xl bg-white/10 p-3">
                <p class="text-[9px] font-bold uppercase opacity-60">Lever</p>
                <p class="font-bold">
                    <i class="wi wi-sunrise text-sm text-yellow-100"></i>
                    {{ formatHour(sunData.sunrise) }}
                </p>
            </div>

            <div class="rounded-2xl bg-white/10 p-3">
                <p class="text-[9px] font-bold uppercase opacity-60">Zénith</p>
                <p class="font-bold">
                    <i class="wi wi-day-sunny text-sm text-yellow-300"></i>
                    {{ formatHour(sunData.solarNoon) }}
                </p>
            </div>

            <div class="rounded-2xl bg-white/10 p-3">
                <p class="text-[9px] font-bold uppercase opacity-60">Coucher</p>
                <p class="font-bold">
                    <i class="wi wi-sunset text-sm text-orange-400"></i>
                    {{ formatHour(sunData.sunset) }}
                </p>
            </div>
        </div>

        <!-- UV -->
        <div class="grid grid-cols-2 gap-3 text-center">
            <div class="rounded-2xl bg-white/10 p-3">
                <p class="text-[9px] font-bold uppercase opacity-60">
                    Durée du jour
                </p>
                <p class="text-sm font-bold">
                    {{ durationBetween(sunData.sunrise, sunData.sunset) }}
                </p>
            </div>
            <div class="rounded-2xl bg-white/10 p-3">
                <p class="text-[9px] font-bold uppercase opacity-60">
                    Index UV
                </p>
                <p :class="uvData.text">{{ uv }} - {{ uvData.label }}</p>
                <div
                    class="relative h-2 overflow-hidden rounded-full bg-gradient-to-r from-green-400 via-orange-400 via-red-500 via-yellow-400 to-purple-600"
                >
                    <div
                        :style="{ left: `calc(${uvPercent}% - 4px)` }"
                        class="absolute top-0 h-2 w-2 rounded-full bg-white shadow"
                    ></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <!-- Boussole -->
            <div
                class="flex flex-col items-center justify-center rounded-2xl bg-white/10 p-3 text-center"
            >
                <svg
                    class="sun-compass mx-auto h-20 w-20"
                    viewBox="0 0 100 100"
                >
                    <!-- Cercle -->
                    <circle
                        :fill="color"
                        cx="50"
                        cy="50"
                        opacity="0.2"
                        r="45"
                        stroke="currentColor"
                        stroke-width="2"
                    />

                    <!-- Axes -->
                    <line
                        opacity="0.2"
                        stroke="currentColor"
                        stroke-width="1"
                        x1="50"
                        x2="50"
                        y1="10"
                        y2="90"
                    />
                    <line
                        opacity="0.2"
                        stroke="currentColor"
                        stroke-width="1"
                        x1="10"
                        x2="90"
                        y1="50"
                        y2="50"
                    />

                    <!-- Points cardinaux -->
                    <text
                        font-size="9"
                        stroke="currentColor"
                        text-anchor="middle"
                        x="50"
                        y="16"
                    >
                        N
                    </text>
                    <text
                        font-size="9"
                        stroke="currentColor"
                        text-anchor="middle"
                        x="84"
                        y="54"
                    >
                        E
                    </text>
                    <text
                        font-size="9"
                        stroke="currentColor"
                        text-anchor="middle"
                        x="50"
                        y="94"
                    >
                        S
                    </text>
                    <text
                        font-size="9"
                        stroke="currentColor"
                        text-anchor="middle"
                        x="16"
                        y="54"
                    >
                        W
                    </text>

                    <!-- Flèche dynamique -->
                    <g
                        :transform="rotation"
                        class="sun-arrow"
                        style="transition: transform 0.6s ease"
                    >
                        <path
                            :fill="color"
                            d="M50 20 L58 40 H52 V75 H48 V40 H42 Z"
                            style="filter: drop-shadow(0 0 4px #facc15)"
                        />
                    </g>
                </svg>
            </div>

            <!-- Infos soleil -->
            <div class="rounded-2xl bg-white/10 p-3">
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Élévation
                    </p>
                    <p class="text-right font-semibold">
                        {{ sunFormatter.altitude ?? '-' }}
                    </p>

                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Latitude
                    </p>
                    <p class="text-right font-semibold">
                        {{ sunFormatter.latitude ?? '-' }}
                    </p>

                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Longitude
                    </p>
                    <p class="text-right font-semibold">
                        {{ sunFormatter.longitude ?? '-' }}
                    </p>

                    <p class="text-[9px] font-bold uppercase opacity-60">
                        Azimut
                    </p>
                    <p class="sun-direction text-right font-semibold">
                        {{ sunFormatter.azimuth_deg }}°
                    </p>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="space-y-2 text-sm">
            <div
                v-for="r in rows"
                :key="r.label"
                :class="r.class"
                class="flex items-center justify-between rounded-xl px-3 py-2"
            >
                <div>
                    <p class="text-xs uppercase">{{ r.label }}</p>
                    <p class="font-semibold">
                        {{ formatRange(r.start, r.end) }}
                    </p>
                </div>

                <div v-if="r.duration && r.duration !== '0h 0m'">
                    <p class="text-xs opacity-60">Durée</p>
                    <p>{{ r.duration }}</p>
                </div>

                <div v-else class="text-right text-xs opacity-50">—</div>
            </div>
        </div>
    </div>
</template>
