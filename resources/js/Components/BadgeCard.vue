<script setup lang="ts">
import { computed } from 'vue';
import BadgeIcon from '@/Components/BadgeIcon.vue';
import type { UserBadge } from '@/types';

const props = defineProps<{
    userBadge: UserBadge;
    showGroup?: boolean;
    compact?: boolean;
}>();

const badge = computed(() => props.userBadge.badge);

const tierLabel = computed(() => {
    const labels: Record<string, string> = {
        standard: '',
        bronze: 'Bronze',
        silver: 'Silver',
        gold: 'Gold',
        platinum: 'Platinum',
    };
    return labels[badge.value.tier] || '';
});

const categoryIcon = computed(() => {
    const icons: Record<string, string> = {
        events: '📅',
        challenges: '🎯',
        wagers: '🎲',
        disputes: '⚖️',
    };
    return icons[badge.value.category] || '🏆';
});

const formattedDate = computed(() => {
    const date = new Date(props.userBadge.awarded_at);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
});

const tierBadgeClasses = computed(() => {
    if (badge.value.is_shame) {
        return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300';
    }

    switch (badge.value.tier) {
        case 'platinum': return 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300';
        case 'gold': return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300';
        case 'silver': return 'bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300';
        case 'bronze': return 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300';
        default: return '';
    }
});
</script>

<template>
    <div
        class="flex items-start gap-3 p-3 rounded-lg bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors"
        :class="{ 'p-2': compact }"
    >
        <BadgeIcon
            :image-url="badge.image_url"
            :tier="badge.tier"
            :name="badge.name"
            :is-shame="badge.is_shame"
            :size="compact ? 'sm' : 'md'"
        />

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h4 class="font-medium text-neutral-900 dark:text-neutral-100 truncate">
                    {{ badge.name }}
                </h4>
                <span
                    v-if="tierLabel"
                    class="text-xs px-1.5 py-0.5 rounded font-medium"
                    :class="tierBadgeClasses"
                >
                    {{ tierLabel }}
                </span>
                <span
                    v-if="badge.is_shame"
                    class="text-xs px-1.5 py-0.5 rounded font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300"
                >
                    Shame
                </span>
            </div>

            <p
                v-if="!compact"
                class="text-sm text-neutral-600 dark:text-neutral-400 mt-0.5 line-clamp-2"
            >
                {{ badge.description }}
            </p>

            <div class="flex items-center gap-2 mt-1 text-xs text-neutral-500 dark:text-neutral-500">
                <span>{{ categoryIcon }} {{ formattedDate }}</span>
                <span v-if="showGroup && userBadge.group_name" class="truncate">
                    &bull; {{ userBadge.group_name }}
                </span>
            </div>
        </div>
    </div>
</template>
