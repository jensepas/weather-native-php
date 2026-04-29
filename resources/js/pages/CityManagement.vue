<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import SearchBar from '@/components/SearchBar.vue';
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
            // Re-fetch cities after deleting to update the list
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
                <div class="mb-6 z-50">
                    <SearchBar @addCity="addCity" />
                </div>

                <div v-if="loading" class="text-center text-white">
                    <div
                        class="h-8 w-8 animate-spin rounded-full border-2 border-white/30 border-t-white"
                    ></div>
                </div>

                <div
                    v-else-if="userCities.length > 0"
                    class="rounded-2xl bg-white/10 p-4"
                >
                    <ul>
                        <li
                            v-for="city in userCities"
                            :key="city.id"
                            class="group mb-2 flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3"
                        >
                            <div class="flex flex-col">
                                <span class="text-base font-medium text-white">
                                    {{ city.name }}
                                </span>
                                <span class="text-xs opacity-60">
                                    {{ city.admin1 }}, {{ city.country }}
                                </span>
                                <span class="text-xs opacity-50">
                                    Coordonnées : {{ city.latitude }},
                                    {{ city.longitude }} <br />
                                    Altitude : {{ city.elevation }}m
                                </span>
                                <span class="text-xs opacity-50">
                                    Population :
                                    {{
                                        city.population?.toLocaleString(
                                            'fr-FR',
                                        ) || '0'
                                    }}
                                    hab
                                </span>
                            </div>

                            <button
                                @click="confirmDeleteCity(city.id, city.name)"
                                class="flex h-9 w-9 items-center justify-center rounded-full text-red-400"
                            >
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </li>
                    </ul>
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
