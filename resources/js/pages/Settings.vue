<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { useSettingsStore } from '@/stores/settingsStore';

const { theme, toggleTheme } = useTheme();
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
                            @click="toggleTheme"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                            :class="theme === 'dark' ? 'bg-indigo-500' : 'bg-zinc-400'"
                        >
                            <span
                                :class="theme === 'dark' ? 'translate-x-6' : 'translate-x-1'"
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                            />
                        </button>
                    </div>
                </section>

                <!-- Unités -->
                <section class="rounded-3xl bg-white/10 p-6 backdrop-blur-md">
                    <h2 class="mb-4 text-sm font-bold uppercase opacity-40">
                        Unités
                    </h2>
                    <div class="space-y-4">
                        <label class="flex cursor-pointer items-center justify-between group">
                            <span class="text-base font-medium">Métrique (km, °C)</span>
                            <input
                                type="radio"
                                value="metric"
                                v-model="settingsStore.selectedUnits"
                                class="h-5 w-5 border-white/20 bg-white/10 text-indigo-500 focus:ring-0 focus:ring-offset-0"
                            />
                        </label>
                        <div class="h-px bg-white/5"></div>
                        <label class="flex cursor-pointer items-center justify-between group">
                            <span class="text-base font-medium">Impérial (miles, °F)</span>
                            <input
                                type="radio"
                                value="imperial"
                                v-model="settingsStore.selectedUnits"
                                class="h-5 w-5 border-white/20 bg-white/10 text-indigo-500 focus:ring-0 focus:ring-offset-0"
                            />
                        </label>
                    </div>
                </section>

                <footer class="text-center text-xs opacity-40">
                    &copy; {{ new Date().getFullYear() }} MSO Météo. Tous droits réservés.
                </footer>
            </main>
        </div>
    </div>
</template>
