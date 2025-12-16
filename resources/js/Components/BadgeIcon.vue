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
</script>

<template>
    <div
        class="relative inline-block rounded-full overflow-hidden bg-neutral-100 dark:bg-neutral-800"
        :class="sizeClasses"
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
