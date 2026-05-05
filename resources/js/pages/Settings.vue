<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { useSettingsStore } from '@/stores/settingsStore';

const { setAuto, setLight, setDark, mode } = useTheme();
const settingsStore = useSettingsStore();
</script>

<template>
    <div
        class="flex h-screen flex-col bg-blue-400 text-black transition-all dark:bg-zinc-950 dark:text-white"
    >
        <!-- Conteneur scrollable -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto p-6">
            <header
                class="mx-auto mb-10 flex max-w-2xl items-center justify-between"
            >
                <h1 class="text-2xl font-semibold tracking-tight">
                    Paramètres
                </h1>

                <Link
                    href="/"
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 backdrop-blur transition hover:bg-white/20 active:scale-90"
                >
                    <i class="fas fa-times"></i>
                </Link>
            </header>

            <main class="mx-auto max-w-2xl space-y-6 pb-12">
                <!-- Apparence -->
                <section class="rounded-3xl bg-white/10 p-6 backdrop-blur-md">
                    <h2 class="mb-4 text-sm font-bold uppercase opacity-40">
                        Apparence
                    </h2>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-medium">Mode Sombre</span>
                        <button
                            @click="setAuto()"
                            :class="mode === 'auto' ? 'bg-white/20' : ''"
                            class="rounded-xl px-3 py-1"
                        >
                            Auto
                        </button>

                        <button
                            @click="setLight()"
                            :class="mode === 'light' ? 'bg-white/20' : ''"
                            class="rounded-xl px-3 py-1"
                        >
                            Light
                        </button>

                        <button
                            @click="setDark()"
                            :class="mode === 'dark' ? 'bg-white/20' : ''"
                            class="rounded-xl px-3 py-1"
                        >
                            Dark
                        </button>
                    </div>
                </section>

                <!-- Unités -->
                <section class="rounded-3xl bg-white/10 p-6 backdrop-blur-md">
                    <h2 class="mb-4 text-sm font-bold uppercase opacity-40">
                        Unités
                    </h2>
                    <div class="space-y-4">
                        <label
                            class="group flex cursor-pointer items-center justify-between"
                        >
                            <span class="text-base font-medium"
                                >Métrique (km, °C)</span
                            >
                            <input
                                type="radio"
                                value="metric"
                                v-model="settingsStore.selectedUnits"
                                class="h-5 w-5 border-white/20 bg-white/10 text-indigo-500 focus:ring-0 focus:ring-offset-0"
                            />
                        </label>
                        <div class="h-px bg-white/5"></div>
                        <label
                            class="group flex cursor-pointer items-center justify-between"
                        >
                            <span class="text-base font-medium"
                                >Impérial (miles, °F)</span
                            >
                            <input
                                type="radio"
                                value="imperial"
                                v-model="settingsStore.selectedUnits"
                                class="h-5 w-5 border-white/20 bg-white/10 text-indigo-500 focus:ring-0 focus:ring-offset-0"
                            />
                        </label>
                    </div>
                </section>
                <!-- Unités -->
                <section class="rounded-3xl bg-white/10 p-6 backdrop-blur-md">
                    <h2 class="mb-4 text-sm font-bold uppercase opacity-40">
                        Localisation
                    </h2>
                    <div class="space-y-4">
                        <label
                            class="group flex cursor-pointer items-center justify-between"
                        >
                            <span class="text-base font-medium"
                                >Format GPS DD (Degrés décimaux)
                            </span>
                            <input
                                type="radio"
                                value="DD"
                                v-model="settingsStore.selectedGPS"
                                class="h-5 w-5 border-white/20 bg-white/10 text-indigo-500 focus:ring-0 focus:ring-offset-0"
                            />
                        </label>
                        <div class="h-px bg-white/5"></div>
                        <label
                            class="group flex cursor-pointer items-center justify-between"
                        >
                            <span class="text-base font-medium"
                                >DMS (Degrés, Minutes, Secondes)</span
                            >
                            <input
                                type="radio"
                                value="DMS"
                                v-model="settingsStore.selectedGPS"
                                class="h-5 w-5 border-white/20 bg-white/10 text-indigo-500 focus:ring-0 focus:ring-offset-0"
                            />
                        </label>
                    </div>
                </section>
                <footer class="text-center text-xs opacity-40">
                    &copy; {{ new Date().getFullYear() }} MSO Météo. Tous droits
                    réservés.
                </footer>
            </main>
        </div>
    </div>
</template>
