<script setup lang="ts">
import { computed } from 'vue';
import type { BadgeTier } from '@/types';

const props = defineProps<{
    imageUrl: string;
    tier: BadgeTier;
    name: string;
    isShame?: boolean;
    size?: 'sm' | 'md' | 'lg' | 'xl';
}>();

const sizeClasses = computed(() => {
    switch (props.size || 'md') {
        case 'sm': return 'w-8 h-8';
        case 'lg': return 'w-16 h-16';
        case 'xl': return 'w-24 h-24';
        default: return 'w-12 h-12';
    }
});

const tierClasses = computed(() => {
    if (props.isShame) {
        return 'ring-red-400 dark:ring-red-600';
    }

    switch (props.tier) {
        case 'platinum': return 'ring-purple-300 dark:ring-purple-400';
        case 'gold': return 'ring-yellow-400 dark:ring-yellow-500';
        case 'silver': return 'ring-neutral-300 dark:ring-neutral-400';
        case 'bronze': return 'ring-orange-400 dark:ring-orange-500';
        default: return 'ring-neutral-200 dark:ring-neutral-600';
    }
});

const tierGlow = computed(() => {
    if (props.isShame) return '';

    switch (props.tier) {
        case 'platinum': return 'shadow-purple-300/50 dark:shadow-purple-500/30';
        case 'gold': return 'shadow-yellow-400/50 dark:shadow-yellow-500/30';
        default: return '';
    }
});
</script>

<template>
    <div
        class="relative inline-block rounded-full ring-2 overflow-hidden bg-neutral-100 dark:bg-neutral-800"
        :class="[sizeClasses, tierClasses, tierGlow && 'shadow-lg ' + tierGlow]"
        :title="name"
    >
        <img
            :src="imageUrl"
            :alt="name"
            class="w-full h-full object-cover"
            loading="lazy"
        />
    </div>
</template>
