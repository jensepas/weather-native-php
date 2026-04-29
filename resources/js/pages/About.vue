<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
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
                    À propos
                </h1>

                <Link
                    href="/"
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 backdrop-blur transition hover:bg-white/20 active:scale-90"
                >
                    <i class="fas fa-times"></i>
                </Link>
            </header>

            <main class="mx-auto max-w-2xl space-y-8 pb-12">
                <section class="rounded-3xl bg-white/10 p-6 backdrop-blur-md">
                    <div class="mb-4 flex items-center gap-4">
                        <div
                            class="flex h-24 w-24 items-center justify-center rounded-2xl bg-white/10"
                        >
                            <img
                                alt="logo"
                                src="/images/mso-meteo-logo.png"
                                class="h-20 w-20 object-contain"
                            />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">MSO Météo</h2>
                            <p class="text-sm opacity-60">Version 1.0.0</p>
                        </div>
                    </div>
                    <p class="leading-relaxed">
                        MSO Météo est une application moderne conçue pour vous
                        fournir des prévisions météorologiques précises et
                        élégantes. Profitez d'une interface intuitive avec des
                        mises à jour en temps réel pour vos villes préférées.
                    </p>
                </section>

                <section class="space-y-4">
                    <h3 class="text-lg font-bold">Fonctionnalités</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white/5 p-4">
                            <i class="fas fa-bolt mb-2 text-yellow-400"></i>
                            <h4 class="text-sm font-bold">Temps Réel</h4>
                            <p class="text-xs opacity-60">
                                Données actualisées instantanément.
                            </p>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4">
                            <i class="fas fa-moon mb-2 text-indigo-400"></i>
                            <h4 class="text-sm font-bold">Mode Sombre</h4>
                            <p class="text-xs opacity-60">
                                Confort visuel garanti de jour comme de nuit.
                            </p>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4">
                            <i class="fas fa-wifi mb-2 text-orange-400"></i>
                            <h4 class="text-sm font-bold">Mode Hors-ligne</h4>
                            <p class="text-xs opacity-60">
                                Consultez vos dernières données sans connexion.
                            </p>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4">
                            <i
                                class="fas fa-map-marker-alt mb-2 text-red-400"
                            ></i>
                            <h4 class="text-sm font-bold">Multi-villes</h4>
                            <p class="text-xs opacity-60">
                                Gérez facilement plusieurs emplacements.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl bg-white/10 p-6 backdrop-blur-md">
                    <h3 class="mb-4 text-lg font-bold">Sources des données</h3>
                    <div class="space-y-4 text-sm leading-relaxed opacity-80">
                        <p>
                            Les données météorologiques et de localisation
                            utilisées dans MSO Météo proviennent de la
                            plateforme Open-Meteo, reconnue pour sa précision et
                            sa fiabilité.
                        </p>
                        <div class="space-y-3">
                            <div class="rounded-2xl bg-white/5 p-4">
                                <h4 class="font-bold">Données météo</h4>
                                <p class="text-xs opacity-60">
                                    Fournies par l'API Open-Meteo, incluant les
                                    prévisions, températures, vent, UV et autres
                                    indicateurs atmosphériques.
                                </p>
                                https://open-meteo.com/en/docs
                            </div>
                            <div class="rounded-2xl bg-white/5 p-4">
                                <h4 class="font-bold">Géolocalisation</h4>
                                <p class="text-xs opacity-60">
                                    Les recherches de villes et coordonnées sont
                                    gérées via l'API de géocodage Open-Meteo.
                                </p>

                                https://open-meteo.com/en/docs/geocoding-api
                            </div>
                        </div>
                        <p class="text-xs opacity-50">
                            Open-Meteo est un service open-source et gratuit ne
                            nécessitant pas de clé API.
                        </p>
                    </div>
                </section>

                <section class="rounded-3xl bg-white/10 p-6 backdrop-blur-md">
                    <h3 class="mb-4 text-lg font-bold">
                        Données astronomiques
                    </h3>

                    <div class="space-y-4 text-sm leading-relaxed opacity-80">
                        <p>
                            MSO Météo intègre également des informations
                            astronomiques calculées localement afin de compléter
                            les prévisions météorologiques.
                        </p>

                        <div class="space-y-3">
                            <div class="rounded-2xl bg-white/5 p-4">
                                <h4 class="font-bold">Cycle lunaire</h4>
                                <p class="text-xs opacity-60">
                                    Les phases de la lune sont déterminées à
                                    partir d’un cycle moyen de 29,53 jours,
                                    permettant d’estimer avec précision la
                                    position actuelle de la lune.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white/5 p-4">
                                <h4 class="font-bold">
                                    Lever et coucher de la lune
                                </h4>
                                <p class="text-xs opacity-60">
                                    Les horaires de lever et de coucher sont
                                    calculés en fonction de la date, de la
                                    position géographique et d’algorithmes
                                    astronomiques simplifiés.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white/5 p-4">
                                <h4 class="font-bold">Position solaire</h4>
                                <p class="text-xs opacity-60">
                                    Les informations liées au soleil (lever,
                                    coucher, durée du jour) sont utilisées pour
                                    enrichir l’expérience utilisateur et
                                    améliorer la compréhension des cycles
                                    naturels.
                                </p>
                            </div>
                        </div>

                        <p class="text-xs opacity-50">
                            Ces calculs sont réalisés directement dans
                            l’application et peuvent présenter de légères
                            variations par rapport aux données officielles.
                        </p>
                    </div>
                </section>

                <section class="rounded-3xl bg-white/10 p-6 backdrop-blur-md">
                    <h3 class="mb-4 text-lg font-bold">Crédits</h3>
                    <p class="text-sm leading-relaxed opacity-80">
                        Développé avec passion en utilisant Laravel, Vue.js et
                        Tailwind CSS. Les données météorologiques sont fournies
                        par des services API de confiance.
                    </p>
                </section>

                <footer class="text-center text-xs opacity-40">
                    &copy; {{ new Date().getFullYear() }} MSO Météo. Tous droits
                    réservés.
                </footer>
            </main>
        </div>
    </div>
</template>
