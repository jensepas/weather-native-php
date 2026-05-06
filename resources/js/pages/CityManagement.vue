<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import draggable from 'vuedraggable'; // Import draggable
import SearchBar from '@/components/SearchBar.vue';
import { useSettingsStore } from '@/stores/settingsStore';
import type { City } from '@/types/WeatherResponse';

const props = defineProps<{
    cities: City[];
}>();

const userCities = ref<City[]>(props.cities);
const loading = ref(false);

const addCity = async (cityData: any) => {
    loading.value = true;
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    try {
        const response = await fetch(`/api/city`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
            },
            body: JSON.stringify({
                cityData: cityData,
            }),
        });

        const data = await response.json();

        if (data.success) {
            // Re-fetch cities after adding to update the list
            await fetchCities();
        }
    } catch (error) {
        console.error('Error adding city:', error);
    } finally {
        loading.value = false;
    }
};

const deleteCity = async (id: string) => {
    loading.value = true;
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    try {
        const res = await fetch(`/api/city/?id=${id}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
            },
        });
        const data = await res.json();

        if (data.success) {
            await fetchCities();
        }
    } catch (error) {
        console.error('Error deleting city:', error);
    } finally {
        loading.value = false;
    }
};

const fetchCities = async () => {
    loading.value = true;

    try {
        const response = await fetch('/api/cities-list', {
            // Assuming a new API endpoint for just listing cities
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        userCities.value = data.cities;
    } catch (error) {
        console.error('Error fetching cities:', error);
    } finally {
        loading.value = false;
    }
};

const validateDelete = () => {
    if (cityToDelete.value) {
        deleteCity(cityToDelete.value.id);
    }

    cancelDelete();
};

const showConfirmModal = ref(false);
const cityToDelete = ref<{ id: string; name: string } | null>(null);

const confirmDeleteCity = (cityId: string, cityName: string) => {
    cityToDelete.value = { id: cityId, name: cityName };
    showConfirmModal.value = true;
};

const cancelDelete = () => {
    showConfirmModal.value = false;
    cityToDelete.value = null;
};

// New function to handle city reordering
const handleCityReorder = async () => {
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    try {
        const cityIds = userCities.value.map((city) => city.id);
        await fetch('/api/cities/reorder', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
            },
            body: JSON.stringify({ cityIds }),
        });
        // No need to re-fetch, userCities is already updated by vuedraggable
    } catch (error) {
        console.error('Error reordering cities:', error);
        // Optionally, revert userCities to previous state or re-fetch
        await fetchCities();
    }
};

function toDMS(decimal: number, type: string, precision = 2): string {
    const absolute = Math.abs(decimal);

    const degrees = Math.floor(absolute);
    const minutesFloat = (absolute - degrees) * 60;
    const minutes = Math.floor(minutesFloat);
    const seconds = ((minutesFloat - minutes) * 60).toFixed(precision);
    let direction = '';

    if (type === 'lat') {
        direction = decimal >= 0 ? 'N' : 'S';
    } else if (type === 'lon') {
        direction = decimal >= 0 ? 'E' : 'W';
    }

    return `${degrees}°${String(minutes).padStart(2, '0')}′${seconds.padStart(precision + 3, '0')}″ ${direction}`;
}
function getGMTOffset(timeZone: string) {
    // 1) On crée la date en UTC (référence neutre)
    const date = new Date();

    // 2) Offset GMT réel (ex : GMT-10)
    const offsetFormatter = new Intl.DateTimeFormat('en-US', {
        timeZone,
        timeZoneName: 'shortOffset',
    });
    const offsetParts = offsetFormatter.formatToParts(date);
    const offset =
        offsetParts.find((p) => p.type === 'timeZoneName')?.value ?? '';
    let gmt = offset.replace('UTC', 'GMT');

    // 3) Nom du fuseau local (HST, AKST, AKDT, MHT…)
    const tzNameFormatter = new Intl.DateTimeFormat('en-US', {
        timeZone,
        timeZoneName: 'short',
    });
    const tzParts = tzNameFormatter.formatToParts(date);
    let tzName = tzParts.find((p) => p.type === 'timeZoneName')?.value ?? '';

    const localDateTime = new Intl.DateTimeFormat('fr-FR', {
        timeZone,
        hour12: false,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    }).format(date);

    tzName = tzName.replace('UTC', 'GMT');
    gmt = gmt.replace(tzName, '');

    return [localDateTime, tzName, gmt, timeZone];
}

const settingsStore = useSettingsStore();

const convertLocation = (decimal: number, type: string) => {
    if (settingsStore.selectedGPS !== 'DD') {
        return toDMS(decimal, type);
    }

    return `${decimal}°`;
};
</script>

<template>
    <transition name="fade">
        <div
            v-if="showConfirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        >
            <div
                class="m-8 w-full max-w-sm rounded-3xl bg-white p-6 text-black shadow-xl dark:bg-zinc-900 dark:text-white"
            >
                <h3 class="mb-2 text-lg font-bold">Supprimer la ville</h3>

                <p class="mb-6 text-sm opacity-70">
                    Voulez-vous vraiment supprimer
                    <span class="font-bold"> "{{ cityToDelete?.name }}" </span>
                    ?
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        @click="cancelDelete"
                        class="rounded-xl bg-zinc-200 px-4 py-2 text-sm transition hover:bg-zinc-300 dark:bg-zinc-700 dark:hover:bg-zinc-600"
                    >
                        Annuler
                    </button>

                    <button
                        @click="validateDelete"
                        class="rounded-xl bg-red-500 px-4 py-2 text-sm text-white transition hover:bg-red-600 active:scale-95"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </transition>

    <div
        :class="['flex h-screen flex-col transition-all']"
        class="bg-blue-400 text-black dark:bg-zinc-950 dark:text-white"
    >
        <!-- Conteneur scrollable -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto p-6">
            <header
                class="mx-auto mb-10 flex max-w-2xl items-center justify-between"
            >
                <h1 class="text-2xl font-semibold tracking-tight">
                    Mes villes
                </h1>

                <Link
                    href="/"
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 backdrop-blur transition hover:bg-white/20 active:scale-90"
                >
                    <i class="fas fa-times"></i>
                </Link>
            </header>

            <main class="mx-auto max-w-2xl space-y-8 pb-12">
                <div class="z-50 mb-6">
                    <SearchBar @addCity="addCity" />
                </div>
                <div v-if="loading" class="text-center text-white">
                    <div
                        class="h-15 w-15 animate-spin rounded-full border-2 border-white/30 border-t-white"
                    ></div>
                </div>

                <div
                    v-else-if="userCities.length > 0"
                    class="rounded-2xl bg-white/10 p-4"
                >
                    <draggable
                        v-model="userCities"
                        tag="ul"
                        item-key="id"
                        @end="handleCityReorder"
                        handle=".drag-handle"
                        delay="150"
                    >
                        <template #item="{ element: city }">
                            <li
                                :key="city.id"
                                class="group mb-2 flex items-center justify-between rounded-2xl bg-white/5 px-2 py-2"
                            >
                                <div class="flex w-full items-center">
                                    <!-- Drag Handle -->
                                    <div
                                        class="drag-handle mr-3 cursor-grab text-white/50 hover:text-white"
                                    >
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    <Link
                                        :href="'./?city=' + city.id"
                                        class="mr-3 flex gap-1"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                    </Link>

                                    <div
                                        @click="city.open = !city.open"
                                        class="flex w-full flex-col"
                                    >
                                        <div
                                            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <span
                                                class="text-base font-medium text-white"
                                            >
                                                {{ city.name }}
                                            </span>
                                            <span class="text-xs opacity-60">
                                                {{
                                                    [city.admin1, city.country]
                                                        .filter(Boolean)
                                                        .join(', ')
                                                }}
                                            </span>
                                        </div>
                                        <transition name="fade">
                                            <div
                                                v-if="city.open"
                                                class="mt-2 text-xs opacity-60"
                                            >
                                                <div class="text-xs opacity-50">
                                                    {{
                                                        getGMTOffset(
                                                            city.timezone ?? '',
                                                        )[1]
                                                    }} —
                                                    {{
                                                        getGMTOffset(
                                                            city.timezone ?? '',
                                                        )[3]
                                                    }}
                                                </div>
                                                <div class="text-xs opacity-50">

                                                    {{
                                                        convertLocation(
                                                            city.latitude,
                                                            'lat',
                                                        )
                                                    }},
                                                    {{
                                                        convertLocation(
                                                            city.longitude,
                                                            'lon',
                                                        )
                                                    }}
                                                    <br />
                                                    Altitude :
                                                    {{ city.elevation }}m
                                                </div>
                                                <div class="text-xs opacity-50">
                                                    Population :
                                                    {{
                                                        city.population?.toLocaleString(
                                                            'fr-FR',
                                                        ) || '0'
                                                    }}
                                                    hab
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </div>

                                <button
                                    @click="
                                        confirmDeleteCity(city.id, city.name)
                                    "
                                    class="flex h-11 w-11 items-center justify-center rounded-full text-red-400"
                                >
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </li>
                        </template>
                    </draggable>
                </div>
                <div
                    v-else
                    class="flex flex-col items-center justify-center px-6 text-center"
                >
                    <div class="item-center relative mb-8 text-center">
                        <div
                            class="absolute inset-0 animate-ping rounded-full bg-white/5"
                        ></div>
                        <div
                            class="text-4xl-xl relative flex h-64 w-64 items-center justify-center rounded-full bg-white/10"
                        >
                            <img alt="logo" src="/images/mso-meteo-logo.png" />
                        </div>
                    </div>

                    <h1 class="text-xl font-bold text-white">
                        Aucune ville sélectionnée
                    </h1>

                    <p
                        class="mt-3 max-w-xs text-sm leading-relaxed text-white/50"
                    >
                        Recherchez une ville pour explorer la météo et les
                        prévisions détaillées.
                    </p>
                </div>
                <footer class="text-center text-xs opacity-40">
                    &copy; {{ new Date().getFullYear() }} MSO Météo. Tous droits
                    réservés.
                </footer>
            </main>
        </div>
    </div>
</template>
