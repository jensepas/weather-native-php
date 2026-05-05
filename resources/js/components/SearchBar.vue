<script lang="ts" setup>
import { ref } from 'vue';

const query = ref('');
const results = ref<any[]>([]);

let timeout: any;


const search = (e: Event) => {
    clearTimeout(timeout);

    const value = (e.target as HTMLInputElement).value;

    if (value.length < 2) {
        results.value = [];

        return;
    }

    timeout = setTimeout(async () => {
        const res = await fetch(`api/search?q=${encodeURIComponent(value)}`);
        results.value = await res.json();
    }, 300);
};

const emit = defineEmits(['addCity']);

const selectCity = (city: any) => {
    emit('addCity', {
        cityData: JSON.stringify(city),
    });

    query.value = '';
    results.value = [];
};
</script>

<template>
    <div class="relative z-50">
        <div class="group relative">
            <input
                v-model="query"
                class="w-full rounded-2xl border border-black/40  px-4 py-1 text-sm placeholder-black/40 transition-all bg-white focus:outline-none dark:bg-black dark:text-white dark:placeholder-white/40 dark:border-white/40 "
                placeholder="Ajouter une ville..."
                @input="search($event)"
            />
        </div>

        <!-- Résultats de recherche flottants -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="results.length"
                class="shadow-2xl-xl absolute top-full mb-3 w-full overflow-hidden rounded-3xl border border-white/10 bg-blue-400 p-2 dark:bg-zinc-900"
            >
                <div
                    v-for="city in results"
                    :key="city.id"
                    class="group flex cursor-pointer items-center gap-3 rounded-2xl px-4 py-3 transition-all hover:bg-white/10 active:scale-98"
                    @click="selectCity(city)"
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 group-hover:bg-indigo-500/20"
                    >
                        <i
                            class="fas fa-location-dot text-white/40 group-hover:text-indigo-400"
                        ></i>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="truncate text-sm font-bold dark:text-white">
                            {{ city.name }}
                        </div>
                        <div
                            class="truncate text-[8px] tracking-wider text-white/40 uppercase"
                        >
                            {{ city.admin1 || '' }}{{ city.admin1 ? ', ' : ''
                            }}{{ city.country }}
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
