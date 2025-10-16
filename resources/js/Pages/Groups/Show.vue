<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Drawer from '@/Components/Drawer.vue';
import GroupSettingsForm from '@/Components/GroupSettingsForm.vue';

const props = defineProps<{
    group: {
        id: string;
        name: string;
        description?: string;
        currency: string;
        starting_balance: number;
        decay_enabled: boolean;
        points_currency_name: string;
        notification_preferences: {
            birthday_reminders: boolean;
            event_reminders: boolean;
            wager_reminders: boolean;
            weekly_summaries: boolean;
        };
        bot_tone?: string;
        has_llm_configured: boolean;
    };
    members: Array<{
        id: string;
        name: string;
        telegram_username?: string;
        balance: number;
        points_earned: number;
        points_spent: number;
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
}>();

// Drawer state
const showSettingsDrawer = ref(false);

// Handle settings update
const handleSettingsUpdated = () => {
    // Reload the page to get fresh data
    router.reload({ only: ['group'] });
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

                    <!-- Back to Dashboard Button -->
                    <div class="mb-4">
                        <a href="/me?focus=groups" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                            ← Back to Dashboard
                        </a>
                    </div>

                    <!-- Settings Button -->
                    <div>
                        <button
                            @click="showSettingsDrawer = true"
                            class="block w-full border-2 border-neutral-300 dark:border-neutral-600 hover:border-neutral-400 dark:hover:border-neutral-500 text-neutral-700 dark:text-neutral-300 font-semibold py-3 px-4 rounded-lg text-center transition-colors"
                        >
                            ⚙️ Group Settings
                        </button>
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
                                    <span>Earned: <span class="font-medium text-green-600 dark:text-green-400">{{ member.points_earned }}</span></span>
                                    <span>Spent: <span class="font-medium text-red-600 dark:text-red-400">{{ member.points_spent }}</span></span>
                                </div>
                                <!-- Mobile: Show earned/spent below -->
                                <div class="sm:hidden">
                                    Earned: <span class="font-medium text-green-600 dark:text-green-400">{{ member.points_earned }}</span>
                                    <span class="mx-1">•</span>
                                    Spent: <span class="font-medium text-red-600 dark:text-red-400">{{ member.points_spent }}</span>
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

                        <!-- Back to Dashboard Button -->
                        <div class="mb-4">
                            <a href="/me?focus=groups" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                                ← Back to Dashboard
                            </a>
                        </div>

                        <!-- Settings Button -->
                        <div>
                            <button
                                @click="showSettingsDrawer = true"
                                class="block w-full border-2 border-neutral-300 dark:border-neutral-600 hover:border-neutral-400 dark:hover:border-neutral-500 text-neutral-700 dark:text-neutral-300 font-semibold py-3 px-4 rounded-lg text-center transition-colors"
                            >
                                ⚙️ Group Settings
                            </button>
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
                                    <span>Earned: <span class="font-medium text-green-600 dark:text-green-400">{{ member.points_earned }}</span></span>
                                    <span>Spent: <span class="font-medium text-red-600 dark:text-red-400">{{ member.points_spent }}</span></span>
                                </div>
                                <!-- Mobile: Show earned/spent below -->
                                <div class="sm:hidden">
                                    Earned: <span class="font-medium text-green-600 dark:text-green-400">{{ member.points_earned }}</span>
                                    <span class="mx-1">•</span>
                                    Spent: <span class="font-medium text-red-600 dark:text-red-400">{{ member.points_spent }}</span>
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
    </AppLayout>
</template>
