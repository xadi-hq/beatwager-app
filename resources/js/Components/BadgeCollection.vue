<script setup lang="ts">
import { computed, ref } from 'vue';
import BadgeCard from '@/Components/BadgeCard.vue';
import BadgeIcon from '@/Components/BadgeIcon.vue';
import type { UserBadge, BadgeCategory } from '@/types';

const props = defineProps<{
    badges: UserBadge[];
    title?: string;
    showEmpty?: boolean;
    compactView?: boolean;
    showGroup?: boolean;
}>();

const viewMode = ref<'grid' | 'list'>('grid');

const categoryLabels: Record<BadgeCategory, { label: string; icon: string }> = {
    events: { label: 'Events', icon: '📅' },
    challenges: { label: 'Challenges', icon: '🎯' },
    wagers: { label: 'Wagers', icon: '🎲' },
    disputes: { label: 'Disputes', icon: '⚖️' },
    special: { label: 'Special', icon: '⭐' },
};

const badgesByCategory = computed(() => {
    const categories: Record<BadgeCategory, UserBadge[]> = {
        events: [],
        challenges: [],
        wagers: [],
        disputes: [],
        special: [],
    };

    props.badges.forEach(userBadge => {
        const category = userBadge.badge.category;
        if (categories[category]) {
            categories[category].push(userBadge);
        }
    });

    return categories;
});

const hasAnyBadges = computed(() => props.badges.length > 0);

const totalBadgeCount = computed(() => props.badges.length);

const shameBadgeCount = computed(() =>
    props.badges.filter(ub => ub.badge.is_shame).length
);

const positiveBadgeCount = computed(() =>
    props.badges.filter(ub => !ub.badge.is_shame).length
);
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    {{ title || 'Badges' }}
                </h3>
                <span
                    v-if="hasAnyBadges"
                    class="text-sm text-neutral-500 dark:text-neutral-400"
                >
                    {{ totalBadgeCount }} earned
                    <span v-if="shameBadgeCount > 0" class="text-red-500">
                        ({{ shameBadgeCount }} shame)
                    </span>
                </span>
            </div>

            <div v-if="hasAnyBadges" class="flex items-center gap-1">
                <button
                    @click="viewMode = 'grid'"
                    class="p-1.5 rounded transition-colors"
                    :class="viewMode === 'grid'
                        ? 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100'
                        : 'text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300'"
                    title="Grid view"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
                <button
                    @click="viewMode = 'list'"
                    class="p-1.5 rounded transition-colors"
                    :class="viewMode === 'list'
                        ? 'bg-neutral-200 dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100'
                        : 'text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300'"
                    title="List view"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-if="!hasAnyBadges && showEmpty"
            class="text-center py-8 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-700"
        >
            <div class="text-3xl mb-2">🏆</div>
            <p class="text-neutral-600 dark:text-neutral-400">No badges earned yet</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-500 mt-1">
                Complete activities to earn badges!
            </p>
        </div>

        <!-- Badge display -->
        <div v-else-if="hasAnyBadges" class="space-y-6">
            <!-- Grid view: Icons only with tooltips -->
            <template v-if="viewMode === 'grid'">
                <div
                    v-for="(categoryBadges, category) in badgesByCategory"
                    :key="category"
                    v-show="categoryBadges.length > 0"
                    class="space-y-2"
                >
                    <h4 class="text-sm font-medium text-neutral-600 dark:text-neutral-400 flex items-center gap-1.5">
                        <span>{{ categoryLabels[category as BadgeCategory].icon }}</span>
                        <span>{{ categoryLabels[category as BadgeCategory].label }}</span>
                        <span class="text-neutral-400 dark:text-neutral-500">({{ categoryBadges.length }})</span>
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="userBadge in categoryBadges"
                            :key="userBadge.id"
                            class="group relative"
                        >
                            <BadgeIcon
                                :image-url="userBadge.badge.small_image_url"
                                :tier="userBadge.badge.tier"
                                :name="userBadge.badge.name"
                                :is-shame="userBadge.badge.is_shame"
                                size="lg"
                            />
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-neutral-900 dark:bg-neutral-100 text-white dark:text-neutral-900 text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                {{ userBadge.badge.name }}
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- List view: Full cards -->
            <template v-else>
                <div
                    v-for="(categoryBadges, category) in badgesByCategory"
                    :key="category"
                    v-show="categoryBadges.length > 0"
                    class="space-y-2"
                >
                    <h4 class="text-sm font-medium text-neutral-600 dark:text-neutral-400 flex items-center gap-1.5">
                        <span>{{ categoryLabels[category as BadgeCategory].icon }}</span>
                        <span>{{ categoryLabels[category as BadgeCategory].label }}</span>
                        <span class="text-neutral-400 dark:text-neutral-500">({{ categoryBadges.length }})</span>
                    </h4>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <BadgeCard
                            v-for="userBadge in categoryBadges"
                            :key="userBadge.id"
                            :user-badge="userBadge"
                            :compact="compactView"
                            :show-group="showGroup"
                        />
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
