<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Drawer from '@/Components/Drawer.vue';
import GroupSettingsForm from '@/Components/GroupSettingsForm.vue';
import SeasonManagement from '@/Components/SeasonManagement.vue';
import PastSeasons from '@/Components/PastSeasons.vue';
import ScheduledMessagesManager from '@/Components/ScheduledMessagesManager.vue';

interface CurrentSeason {
    id: string;
    season_number: number;
    started_at: string;
    is_active: boolean;
    days_elapsed: number;
}

interface PastSeason {
    id: string;
    season_number: number;
    started_at: string;
    ended_at: string;
    duration_days: number;
    winner: {
        user_id: string;
        name: string;
        points: number;
        rank: number;
    } | null;
    total_participants: number;
    total_wagers: number;
}

const props = defineProps<{
    group: {
        id: string;
        name: string;
        description?: string;
        currency: string;
        starting_balance: number;
        decay_enabled: boolean;
        points_currency_name: string;
        timezone: string;
        language?: string;
        group_type?: string;
        notification_preferences: {
            birthday_reminders: boolean;
            event_reminders: boolean;
            wager_reminders: boolean;
            weekly_summaries: boolean;
        };
        bot_tone?: string;
        llm_provider?: string;
        allow_nsfw?: boolean;
        has_llm_configured: boolean;
        llm_metrics?: {
            total_calls: number;
            cached_calls: number;
            fallback_calls: number;
            estimated_cost_usd: number;
            cache_hit_rate: number;
        };
        current_season: CurrentSeason | null;
        season_ends_at: string | null;
    };
    members: Array<{
        id: string;
        name: string;
        telegram_username?: string;
        balance: number;
        points_earned: number;
        points_spent: number;
        event_attendance_streak: number;
        role: string;
        last_activity_at?: string;
    }>;
    stats: {
        total_members: number;
        total_wagers: number;
        active_wagers: number;
        total_events: number;
        upcoming_events: number;
    };
    userBalance: number;
    pastSeasons: PastSeason[];
}>();

// Drawer state
const showSettingsDrawer = ref(false);
const showSeasonsDrawer = ref(false);
const showMessagesDrawer = ref(false);

// Handle settings update
const handleSettingsUpdated = () => {
    // Reload the page to get fresh data
    router.reload({ only: ['group'] });
};

// Handle season update
const handleSeasonUpdated = () => {
    // Reload the page to get fresh data
    router.reload();
};

// Handle messages update
const handleMessagesUpdated = () => {
    // No need to reload page for messages
};

// Format date for display
const formatDate = (dateString?: string) => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: '2-digit',
        month: 'short',
        day: 'numeric'
    });
};

// Sort members by balance descending
const sortedMembers = [...props.members].sort((a, b) => b.balance - a.balance);
</script>

<template>
    <AppLayout>
        <Head :title="group.name" />

        <div class="max-w-7xl mx-auto px-4 py-12">
            <!-- Mobile/Tablet: Single column layout -->
            <div class="lg:hidden">
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                    <!-- Header -->
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">
                            {{ group.name }}
                        </h1>
                        <p v-if="group.description" class="text-neutral-600 dark:text-neutral-300 mt-2">
                            {{ group.description }}
                        </p>
                    </div>

                    <!-- User Balance -->
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="text-sm text-neutral-600 dark:text-neutral-400">Your Balance</div>
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ userBalance }} {{ group.currency }}
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.total_members }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Members</div>
                        </div>
                        <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.active_wagers }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Active Wagers</div>
                        </div>
                        <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.upcoming_events }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Upcoming Events</div>
                        </div>
                        <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ group.starting_balance }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Starting Balance</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <!-- Top Row: 3 Buttons -->
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Seasons Button -->
                            <button
                                @click="showSeasonsDrawer = true"
                                class="border-2 border-purple-300 dark:border-purple-600 hover:border-purple-400 dark:hover:border-purple-500 text-purple-700 dark:text-purple-300 font-semibold py-3 px-2 rounded-lg text-center transition-colors text-sm"
                            >
                                🏆 Seasons
                                <span v-if="group.current_season" class="block text-xs bg-purple-100 dark:bg-purple-900 px-1 py-0.5 rounded mt-1">
                                    S{{ group.current_season.season_number }}
                                </span>
                            </button>

                            <!-- Messages Button -->
                            <button
                                @click="showMessagesDrawer = true"
                                class="border-2 border-green-300 dark:border-green-600 hover:border-green-400 dark:hover:border-green-500 text-green-700 dark:text-green-300 font-semibold py-3 px-2 rounded-lg text-center transition-colors text-sm"
                            >
                                📅 Messages
                            </button>

                            <!-- Settings Button -->
                            <button
                                @click="showSettingsDrawer = true"
                                class="border-2 border-neutral-300 dark:border-neutral-600 hover:border-neutral-400 dark:hover:border-neutral-500 text-neutral-700 dark:text-neutral-300 font-semibold py-3 px-2 rounded-lg text-center transition-colors text-sm"
                            >
                                ⚙️ Settings
                            </button>
                        </div>

                        <!-- Back to Dashboard - Full Width -->
                        <a href="/me?focus=groups" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Members -->
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">Members ({{ members.length }})</h2>
                    <div class="space-y-2">
                        <div
                            v-for="(member, index) in sortedMembers"
                            :key="member.id"
                            class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600"
                        >
                            <div class="flex items-start justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span v-if="index === 0" class="text-xl">🥇</span>
                                    <span v-else-if="index === 1" class="text-xl">🥈</span>
                                    <span v-else-if="index === 2" class="text-xl">🥉</span>
                                    <span class="font-medium text-neutral-900 dark:text-white">
                                        {{ member.name }}
                                        <span v-if="member.telegram_username" class="text-sm text-neutral-500 dark:text-neutral-400">(@{{ member.telegram_username }})</span>
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-neutral-900 dark:text-white">{{ member.balance }} <span class="text-xs font-normal text-neutral-500 dark:text-neutral-400">{{ group.currency }}</span></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs text-neutral-600 dark:text-neutral-400 pl-8">
                                <div>
                                    Last active: <span class="text-neutral-500 dark:text-neutral-400">{{ formatDate(member.last_activity_at) }}</span>
                                </div>
                                <div class="hidden sm:flex gap-3">
                                    <span>📥 <span class="font-medium text-green-600 dark:text-green-400">{{ member.points_earned }}</span></span>
                                    <span>📤 <span class="font-medium text-red-600 dark:text-red-400">{{ member.points_spent }}</span></span>
                                    <span v-if="member.event_attendance_streak > 0">🔥 <span class="font-medium text-orange-600 dark:text-orange-400">{{ member.event_attendance_streak }}</span></span>
                                </div>
                                <!-- Mobile: Show earned/spent/streak below -->
                                <div class="sm:hidden">
                                    📥 <span class="font-medium text-green-600 dark:text-green-400">{{ member.points_earned }}</span>
                                    <span class="mx-1">•</span>
                                    📤 <span class="font-medium text-red-600 dark:text-red-400">{{ member.points_spent }}</span>
                                    <span v-if="member.event_attendance_streak > 0">
                                        <span class="mx-1">•</span>
                                        🔥 <span class="font-medium text-orange-600 dark:text-orange-400">{{ member.event_attendance_streak }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop: Two column layout -->
            <div class="hidden lg:grid lg:grid-cols-2 lg:gap-6">
                <!-- Left column: Group details -->
                <div>
                    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">
                            {{ group.name }}
                        </h1>
                        <p v-if="group.description" class="text-neutral-600 dark:text-neutral-300 mb-4">
                            {{ group.description }}
                        </p>

                        <!-- User Balance -->
                        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="text-sm text-neutral-600 dark:text-neutral-400">Your Balance</div>
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                {{ userBalance }} {{ group.currency }}
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.total_members }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Members</div>
                            </div>
                            <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.active_wagers }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Active Wagers</div>
                            </div>
                            <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ stats.upcoming_events }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Upcoming Events</div>
                            </div>
                            <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ group.starting_balance }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Starting Balance</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <!-- Top Row: 3 Buttons -->
                            <div class="grid grid-cols-3 gap-2">
                                <!-- Seasons Button -->
                                <button
                                    @click="showSeasonsDrawer = true"
                                    class="border-2 border-purple-300 dark:border-purple-600 hover:border-purple-400 dark:hover:border-purple-500 text-purple-700 dark:text-purple-300 font-semibold py-3 px-2 rounded-lg text-center transition-colors text-sm"
                                >
                                    🏆 Seasons
                                    <span v-if="group.current_season" class="block text-xs bg-purple-100 dark:bg-purple-900 px-1 py-0.5 rounded mt-1">
                                        S{{ group.current_season.season_number }}
                                    </span>
                                </button>

                                <!-- Messages Button -->
                                <button
                                    @click="showMessagesDrawer = true"
                                    class="border-2 border-green-300 dark:border-green-600 hover:border-green-400 dark:hover:border-green-500 text-green-700 dark:text-green-300 font-semibold py-3 px-2 rounded-lg text-center transition-colors text-sm"
                                >
                                    📅 Messages
                                </button>

                                <!-- Settings Button -->
                                <button
                                    @click="showSettingsDrawer = true"
                                    class="border-2 border-neutral-300 dark:border-neutral-600 hover:border-neutral-400 dark:hover:border-neutral-500 text-neutral-700 dark:text-neutral-300 font-semibold py-3 px-2 rounded-lg text-center transition-colors text-sm"
                                >
                                    ⚙️ Settings
                                </button>
                            </div>

                            <!-- Back to Dashboard - Full Width -->
                            <a href="/me?focus=groups" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                                ← Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right column: Members -->
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">Members ({{ members.length }})</h2>
                    <div class="space-y-2 max-h-[800px] overflow-y-auto">
                        <div
                            v-for="(member, index) in sortedMembers"
                            :key="member.id"
                            class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600"
                        >
                            <div class="flex items-start justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span v-if="index === 0" class="text-xl">🥇</span>
                                    <span v-else-if="index === 1" class="text-xl">🥈</span>
                                    <span v-else-if="index === 2" class="text-xl">🥉</span>
                                    <span class="font-medium text-neutral-900 dark:text-white">
                                        {{ member.name }}
                                        <span v-if="member.telegram_username" class="text-sm text-neutral-500 dark:text-neutral-400">(@{{ member.telegram_username }})</span>
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-neutral-900 dark:text-white">{{ member.balance }} <span class="text-xs font-normal text-neutral-500 dark:text-neutral-400">{{ group.currency }}</span></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs text-neutral-600 dark:text-neutral-400 pl-8">
                                <div>
                                    Last active: <span class="text-neutral-500 dark:text-neutral-400">{{ formatDate(member.last_activity_at) }}</span>
                                </div>
                                <div class="hidden sm:flex gap-3">
                                    <span>📥 <span class="font-medium text-green-600 dark:text-green-400">{{ member.points_earned }}</span></span>
                                    <span>📤 <span class="font-medium text-red-600 dark:text-red-400">{{ member.points_spent }}</span></span>
                                    <span v-if="member.event_attendance_streak > 0">🔥 <span class="font-medium text-orange-600 dark:text-orange-400">{{ member.event_attendance_streak }}</span></span>
                                </div>
                                <!-- Mobile: Show earned/spent/streak below -->
                                <div class="sm:hidden">
                                    📥 <span class="font-medium text-green-600 dark:text-green-400">{{ member.points_earned }}</span>
                                    <span class="mx-1">•</span>
                                    📤 <span class="font-medium text-red-600 dark:text-red-400">{{ member.points_spent }}</span>
                                    <span v-if="member.event_attendance_streak > 0">
                                        <span class="mx-1">•</span>
                                        🔥 <span class="font-medium text-orange-600 dark:text-orange-400">{{ member.event_attendance_streak }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Drawer -->
        <Drawer
            :show="showSettingsDrawer"
            :title="`${group.name} Settings`"
            @close="showSettingsDrawer = false"
        >
            <GroupSettingsForm
                :group="group"
                @updated="handleSettingsUpdated"
            />
        </Drawer>

        <!-- Seasons Drawer -->
        <Drawer
            :show="showSeasonsDrawer"
            :title="`${group.name} Seasons`"
            @close="showSeasonsDrawer = false"
        >
            <div class="space-y-6">
                <!-- Season Management -->
                <SeasonManagement
                    :group-id="group.id"
                    :current-season="group.current_season"
                    :season-ends-at="group.season_ends_at"
                    :currency="group.currency"
                    @updated="handleSeasonUpdated"
                />

                <!-- Divider -->
                <div class="border-t border-neutral-200 dark:border-neutral-600"></div>

                <!-- Past Seasons -->
                <PastSeasons
                    :group-id="group.id"
                    :seasons="pastSeasons"
                    :currency="group.currency"
                />
            </div>
        </Drawer>

        <!-- Messages Drawer -->
        <Drawer
            :show="showMessagesDrawer"
            :title="`${group.name} Scheduled Messages`"
            @close="showMessagesDrawer = false"
        >
            <ScheduledMessagesManager
                :group-id="group.id"
                @updated="handleMessagesUpdated"
            />
        </Drawer>
    </AppLayout>
</template>
