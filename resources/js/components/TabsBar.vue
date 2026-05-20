<script lang="ts" setup>
defineProps<{
    cities: { id: string; name: string }[];
    selectedCityName: string;
    selectedCityId: string;
}>();

const emit = defineEmits(['fetchCity']);
</script>

<template>
    <div id="tabsList" class="tabs-container no-scrollbar">
        <div
            v-for="city in cities"
            :key="city.id"
            class="group relative shrink-0"
        >
            <button
                :class="[
                    'tab-button w-4 h-4 relative overflow-hidden rounded-full  px-1 py-1 transition-all duration-300 active:scale-95',
                    selectedCityId == city.id
                        ? 'active-tab scale-105 bg-white text-blue-600 shadow-[0_10px_20px_rgba(255,255,255,0.2)]'
                        : 'bg-white/10 text-white/80 hover:bg-white/20 hover:text-white',
                ]"
                :disabled="selectedCityId == city.id"
                @click="emit('fetchCity', city.id)"
            ></button>
        </div>
    </div>
</template>
