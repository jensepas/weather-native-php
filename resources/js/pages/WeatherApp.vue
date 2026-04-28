<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';
import CurrentDayContent from '@/components/CurrentDayContent.vue';
import ForecastsContent from '@/components/ForecastsContent.vue';
import MoonContent from '@/components/MoonContent.vue';
import SearchBar from '@/components/SearchBar.vue';
import SunContent from '@/components/SunContent.vue';
import TabsBar from '@/components/TabsBar.vue';
import WindContent from '@/components/WindContent.vue';
import { useTheme } from '@/composables/useTheme';
import type { City, WeatherResponse } from '@/types/WeatherResponse';

// Define props to receive initial data from Inertia
const props = defineProps<{
    selectedCityName: string;
    selectedCityId: string;
    selectedCityInfos: City;
}>();

// Initialize refs with prop data
const cities = ref<City[]>([]);
const selectedCityId = ref<string>(props.selectedCityId || '');
const selectedCityName = ref<string>(props.selectedCityName || '');
const weather = ref<WeatherResponse>();
const isOffline = ref(!navigator.onLine);
const isSearching = ref(false);
const transitionName = ref('fade'); // Ajout de la variable pour la transition

const { theme, toggleTheme } = useTheme();
const loading = ref(false);

const updateOnlineStatus = () => {
    isOffline.value = !navigator.onLine;
};

const toggleSearch = () => {
    isSearching.value = !isSearching.value;
};

const addCity = async (cityData: any) => {
    if (isOffline.value) {
        return;
    }

    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    loading.value = true;
    isSearching.value = false;

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
            transitionName.value = 'fade';
            await fetchCity(data.id);
        }
    } finally {
        loading.value = false;
    }
};

const deleteCity = async (id: string) => {
    if (isOffline.value) {
        return;
    }

    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    loading.value = true;

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
            const cityToDelete = cities.value.find((c) => c.id === id);
            const isDeletingSelected =
                cityToDelete?.name === selectedCityName.value;

            if (isDeletingSelected) {
                const remainingCities = cities.value.filter((c) => c.id !== id);

                if (remainingCities.length > 0) {
                    transitionName.value = 'fade'; // Réinitialise la transition pour la suppression
                    await fetchCity(remainingCities[0].id);
                } else {
                    // If no cities left, clear weather data and selected city
                    weather.value = undefined;
                    cities.value = [];
                    selectedCityName.value = '';
                    selectedCityId.value = '';

                    localStorage.removeItem('last_city');
                }
            } else {
                await fetchCity(selectedCityId.value);
            }
        }
    } finally {
        loading.value = false;
    }
};

function updateNav() {
    const activeTab = document.querySelector('.active-tab');

    if (activeTab) {
        activeTab.scrollIntoView({
            behavior: 'smooth',
            inline: 'center',
            block: 'nearest',
        });
    }
}

function updateHourlyList() {
    const container = document.getElementById('hourlyContainer');
    const activeHour = container?.querySelector('.active-hour') as HTMLElement;

    if (container && activeHour) {
        const scrollPos =
            activeHour.offsetTop -
            container.clientHeight / 2 +
            activeHour.clientHeight / 2;
        container.scrollTo({
            top: scrollPos,
            behavior: 'smooth',
        });
    }
}

const fetchCity = async (city: string, force = false) => {
    // ÉTAPE 1 : Si offline, on regarde si on a cette ville en cache

    if (!city || city === 'undefined' || city === 'null') {
        return;
    }

    const cleanCity = city;

    if (!cleanCity) {
        return;
    }

    const oldCityId = selectedCityId.value;

    if (isOffline.value) {
        const cachedData = localStorage.getItem(`weather_cache_${cleanCity}`);

        if (cachedData) {
            try {
                const data = JSON.parse(cachedData);
                weather.value = data;
                cities.value = data.cities;
                selectedCityName.value = data.selectedCityName;
                selectedCityId.value = data.selectedCityId;

                localStorage.setItem('last_city', data.selectedCityId);

                await nextTick();
                updateNav();

                // Si l'ID n'a pas changé, on force l'update car il n'y aura pas de transition
                if (oldCityId === data.selectedCityId) {
                    updateCompasses();
                    updateHourlyList();
                }

                return;
            } catch (e) {
                console.error('Erreur lecture cache offline:', e);
            }
        }

        return; // Pas de réseau et pas de cache pour cette ville, on ne fait rien
    }

    // ÉTAPE 2 : Si online, on fait le fetch normal
    loading.value = true;

    try {
        const res = await fetch(
            `/api/weather/?city=${encodeURIComponent(cleanCity)}&refresh=${force}`,
            {
                headers: { Accept: 'application/json' },
            },
        );

        const data = await res.json();

        weather.value = data;
        cities.value = data.cities;
        selectedCityName.value = data.selectedCityName;
        selectedCityId.value = data.selectedCityId;

        localStorage.setItem('last_city', data.selectedCityId);
        localStorage.setItem(
            `weather_cache_${data.selectedCityId}`,
            JSON.stringify(data),
        );

        await nextTick();

        updateNav();

        // Si l'ID n'a pas changé (refresh), on force l'update car il n'y aura pas de transition
        if (oldCityId === data.selectedCityId) {
            updateCompasses();
            updateHourlyList();
        }
    } catch (error) {
        console.error('Erreur lors du chargement de la météo:', error);
    } finally {
        loading.value = false;
    }
};

const handleAfterEnter = () => {
    updateCompasses();
    updateHourlyList();
};

const handleTabCityFetch = (cityName: string) => {
    transitionName.value = 'fade'; // Utilise une transition fade pour les clics sur les onglets
    fetchCity(cityName);
};

const rotateElements = (
    containerClass: string,
    dirClass: string,
    arrowClass: string,
) => {
    document.querySelectorAll(containerClass).forEach((el) => {
        const dirEl = el.querySelector(dirClass);
        const arrow = el.querySelector(arrowClass);

        if (dirEl && arrow instanceof HTMLElement) {
            const deg = Number.parseFloat(dirEl.textContent || '0');
            arrow.style.transform = `rotate(${deg}deg)`;
        }
    });
};

const updateCompasses = () => {
    rotateElements('.wind-compass', '.wind-direction', '.wind-arrow');
    rotateElements('.sun-compass', '.sun-direction', '.sun-arrow');
};

const setupTouchGestures = () => {
    const scrollArea = document.getElementById('scrollArea');
    const pullIndicator = document.getElementById('pull-indicator');
    const pullIcon = document.getElementById('pull-icon');

    if (!scrollArea || !pullIndicator || !pullIcon) {
        return;
    }

    let touchStartY = 0;
    let touchStartX = 0;
    const pullThreshold = 80;
    const swipeThreshold = 70; // Sensibilité du balayage horizontal

    scrollArea.addEventListener(
        'touchstart',
        (e) => {
            touchStartX = e.touches[0].pageX;
            touchStartY = e.touches[0].pageY;

            if (scrollArea.scrollTop === 0) {
                pullIndicator.classList.remove('transition-all');
            }
        },
        { passive: true },
    );

    scrollArea.addEventListener(
        'touchmove',
        (e) => {
            const touchY = e.touches[0].pageY;
            const touchX = e.touches[0].pageX;
            const pullDistance = touchY - touchStartY;
            const horizontalDistance = Math.abs(touchX - touchStartX);

            // On ne gère le pull to-refresh que si le mouvement est principalement vertical
            if (horizontalDistance < Math.abs(pullDistance)) {
                if (scrollArea.scrollTop === 0 && pullDistance > 0) {
                    const height = Math.min(
                        pullDistance * 0.5,
                        pullThreshold + 20,
                    );
                    pullIndicator.style.height = `${height}px`;
                    const rotation = (pullDistance / pullThreshold) * 180;
                    pullIcon.style.transform = `rotate(${rotation}deg)`;
                    // eslint-disable-next-line @typescript-eslint/no-unused-expressions
                    pullDistance > pullThreshold
                        ? pullIcon.classList.add('text-indigo-200')
                        : pullIcon.classList.remove('text-indigo-200');
                }
            }
        },
        { passive: true },
    );

    scrollArea.addEventListener('touchend', async (e) => {
        const touchEndY = e.changedTouches[0].pageY;
        const touchEndX = e.changedTouches[0].pageX;
        const deltaX = touchEndX - touchStartX;
        const deltaY = touchEndY - touchStartY;

        // Détection du swipe horizontal (Navigation entre villes)
        if (
            Math.abs(deltaX) > Math.abs(deltaY) &&
            Math.abs(deltaX) > swipeThreshold
        ) {
            const currentIndex = cities.value.findIndex(
                (c) => c.id == selectedCityId.value,
            );

            if (currentIndex !== -1) {
                if (deltaX > 0 && currentIndex > 0) {
                    // Swipe à droite -> Ville précédente
                    transitionName.value = 'slide-right'; // Définit la transition pour le swipe droit
                    await fetchCity(cities.value[currentIndex - 1].id);
                } else if (
                    deltaX < 0 &&
                    currentIndex < cities.value.length - 1
                ) {
                    // Swipe à gauche -> Ville suivante
                    transitionName.value = 'slide-left'; // Définit la transition pour le swipe gauche
                    await fetchCity(cities.value[currentIndex + 1].id);
                }
            }
        } else {
            // Détection du Pull-to-refresh
            const pullDistance = touchEndY - touchStartY;

            if (scrollArea.scrollTop === 0 && pullDistance > pullThreshold) {
                pullIcon.classList.add('fa-spin');
                await fetchCity(selectedCityId.value, true);
            }
        }

        // Reset de l'indicateur pull-to-refresh
        pullIndicator.classList.add('transition-all');
        pullIndicator.style.height = '0px';
        pullIcon.classList.remove('fa-spin', 'text-indigo-200');
        pullIcon.style.transform = 'rotate(0deg)';
    });
};

let refreshInterval: number | null | undefined = null;

onMounted(async () => {
    globalThis.addEventListener('online', updateOnlineStatus);
    globalThis.addEventListener('offline', updateOnlineStatus);

    const savedCity = localStorage.getItem('last_city') || selectedCityId.value;
    const cachedData = localStorage.getItem(`weather_cache_${savedCity}`);

    if (cachedData) {
        try {
            const data = JSON.parse(cachedData);
            weather.value = data;
            cities.value = data.cities;
            selectedCityName.value = data.selectedCityName;
            selectedCityId.value = data.selectedCityId;

            await nextTick();
            updateNav();
            updateCompasses();
            updateHourlyList();
        } catch (e) {
            console.error('Erreur lecture cache:', e);
        }
    }

    if (!isOffline.value) {
        await fetchCity(savedCity);
    }

    refreshInterval = setInterval(
        () => {
            if (!isOffline.value) {
                fetchCity(selectedCityId.value);
            }
        },
        5 * 60 * 1000,
    );

    await nextTick();
    updateCompasses();
    setupTouchGestures();
    updateHourlyList();
});

onUnmounted(() => {
    globalThis.removeEventListener('online', updateOnlineStatus);
    globalThis.removeEventListener('offline', updateOnlineStatus);

    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<template>
    <div
        :class="['flex h-screen flex-col transition-all']"
        class="bg-blue-400 text-black dark:bg-zinc-950 dark:text-white"
    >
        <!-- Indicateur Hors-ligne -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform -translate-y-full"
            enter-to-class="transform translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-y-0"
            leave-to-class="transform -translate-y-full"
        >
            <div
                v-if="isOffline"
                class="fixed top-0 z-100 w-full bg-orange-500 py-1 text-center text-xs font-bold text-white shadow-md"
            >
                <i class="fas fa-wifi-slash mr-2"></i> Mode hors-ligne
            </div>
        </transition>

        <!-- Loader -->
        <div v-if="loading && !weather" class="loading-overlay">
            <i class="fas fa-circle-notch fa-spin text-3xl"></i>
        </div>

        <header class="fixed top-8 right-0 z-50 pr-2">
            <!-- Mode Recherche -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
                mode="out-in"
            >
                <div v-if="isSearching" class="flex items-center">
                    <SearchBar class="w-68" @addCity="addCity" />
                    <button
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white hover:bg-white/20 active:scale-90"
                        @click="toggleSearch"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Mode Normal (Tabs + Boutons) -->
                <div v-else class="flex items-center gap-1">
                    <div class="flex-1 overflow-hidden">
                        <div v-if="cities?.length > 0"></div>
                        <div
                            v-else
                            class="px-4 py-2 text-sm font-medium text-white/40"
                        >
                            Prêt pour une recherche ?
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1">
                        <button
                            class="flex h-8 w-8 items-center justify-center rounded-2xl bg-white/10 text-white transition-all hover:bg-white/20 active:scale-90"
                            @click="toggleSearch"
                        >
                            <i class="fas fa-search text-sm"></i>
                        </button>
                        <button
                            class="flex h-8 w-8 items-center justify-center rounded-2xl bg-white/10 text-white transition-all hover:bg-white/20 active:scale-90"
                            @click="toggleTheme"
                        >
                            <i
                                v-if="theme === 'dark'"
                                class="fas fa-moon text-sm"
                            ></i>
                            <i v-else class="fas fa-sun text-sm"></i>
                        </button>
                        <Link
                            :href="'/about'"
                            class="flex h-8 w-8 items-center justify-center rounded-2xl bg-white/10 text-white transition-all hover:bg-white/20 active:scale-90"
                        >
                            <i class="fas fa-info text-sm"></i>
                        </Link>
                    </div>
                </div>
            </transition>
        </header>

        <!-- Content -->
        <main id="scrollArea" class="relative overflow-x-hidden p-4 pt-8">
            <!-- Pull to Refresh Indicator -->
            <div
                id="pull-indicator"
                class="pointer-events-none flex h-0 items-center justify-center overflow-hidden transition-all duration-150"
            >
                <div class="p-2-md mb-4 rounded-full bg-white/20">
                    <i
                        id="pull-icon"
                        class="fas fa-sync-alt text-sm text-white"
                    ></i>
                </div>
            </div>

            <transition
                :name="transitionName"
                mode="out-in"
                @after-enter="handleAfterEnter"
            >
                <div
                    v-if="selectedCityId"
                    id="weatherContent"
                    :key="selectedCityId"
                    class="mx-auto grid grid-cols-2 gap-4 md:grid-cols-4"
                >
                    <CurrentDayContent
                        v-if="weather"
                        :data="weather"
                        @deleteCity="deleteCity"
                    />
                    <ForecastsContent v-if="weather" :data="weather" />

                    <SunContent
                        v-if="weather"
                        :data="weather"
                        :sun-data="weather.astronomy.sun"
                        :sun-formatter="weather.astronomy.sunFormatted"
                        :today-details="weather.weather.todayDetails"
                        :is-day="weather.astronomy.isDay"
                        :local-date="weather.astronomy.localDate"
                    />

                    <MoonContent
                        v-if="weather"
                        :data="weather"
                        :is-day="weather.astronomy.isDay"
                    />
                    <WindContent v-if="weather" :data="weather" />
                </div>

                <div
                    v-else
                    :key="'no-city'"
                    class="flex h-full items-center justify-center"
                >
                    <div class="mx-auto max-w-sm">
                        <div
                            class="flex flex-col items-center justify-center px-6 text-center"
                        >
                            <div class="relative mb-8">
                                <div
                                    class="absolute inset-0 animate-ping rounded-full bg-white/5"
                                ></div>
                                <div
                                    class="text-4xl-xl relative flex h-64 w-64 items-center justify-center rounded-full bg-white/10"
                                >
                                    <img
                                        alt="logo"
                                        src="/images/mso-meteo-logo.png"
                                    />
                                </div>
                            </div>

                            <h1 class="text-xl font-bold text-white">
                                Aucune ville sélectionnée
                            </h1>

                            <p
                                class="mt-3 max-w-xs text-sm leading-relaxed text-white/50"
                            >
                                Recherchez une ville ci-dessous pour explorer la
                                météo et les prévisions détaillées.
                            </p>
                        </div>
                    </div>
                </div>
            </transition>
            <div class="text-center text-xs opacity-40">
                &copy; {{ new Date().getFullYear() }} MSO Météo. Tous droits
                réservés.
            </div>
        </main>

        <!-- Footer -->
        <footer class="fixed right-4 bottom-10 left-4 z-50 p-1 transition-all">
            <div class="mx-auto max-w-md">
                <TabsBar
                    :cities="cities"
                    :selectedCityId="selectedCityId"
                    :selectedCityName="selectedCityName"
                    class="items-center justify-center"
                    @fetchCity="handleTabCityFetch"
                />
            </div>
        </footer>
    </div>
</template>
