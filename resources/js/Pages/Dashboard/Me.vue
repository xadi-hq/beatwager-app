<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DisputeStatusBadge from '@/Components/DisputeStatusBadge.vue';
import BadgeCollection from '@/Components/BadgeCollection.vue';
import type { UserBadge } from '@/types';

const props = defineProps<{
    user: any;
    stats: any;
    groups: any[];
    activeWagers: any[];
    openWagers: any[];
    awaitingSettlement: any[];
    settledWagers: any[];
    recentTransactions: any[];
    upcomingEvents: any[];
    pastProcessedEvents: any[];
    pastUnprocessedEvents: any[];
    userChallenges: any[];
    userBadges: UserBadge[];
    focus: string;
}>();

// Active tab management - default based on focus or wagers if not set
const getDefaultTab = () => {
    if (props.focus === 'transactions') return 'transactions';
    if (props.focus === 'events') return 'events';
    if (props.focus === 'challenges') return 'challenges';
    if (props.focus === 'badges') return 'badges';
    return 'wagers';
};

const activeTab = ref(getDefaultTab());

// Group selector state
const isGroupDropdownOpen = ref(false);
const selectedGroupFilter = ref<string>('all');

// Selected group object
const selectedGroup = computed(() => {
    if (selectedGroupFilter.value === 'all') return null;
    return props.groups.find((g: any) => g.id === selectedGroupFilter.value);
});

// Functions for group selector
function selectGroup(groupId: string | null) {
    isGroupDropdownOpen.value = false;
    selectedGroupFilter.value = groupId || 'all';
}

function viewSelectedGroup() {
    if (selectedGroup.value) {
        router.visit(`/groups/${selectedGroup.value.id}`);
    }
}

// Computed properties for filtered stats
const filteredStats = computed(() => {
    if (selectedGroupFilter.value === 'all') {
        return props.stats;
    }

    // Calculate stats for selected group
    const selectedGroup = props.groups.find(g => g.id === selectedGroupFilter.value);
    const groupBalance = selectedGroup ? selectedGroup.balance : 0;

    // Filter wagers for this group
    const groupWagers = props.activeWagers.filter(w => w.group.id === selectedGroupFilter.value);
    const groupSettled = props.settledWagers.filter(w => w.group.id === selectedGroupFilter.value);

    // Filter challenges for this group
    const groupChallenges = props.userChallenges.filter(c =>
        c.group.id === selectedGroupFilter.value &&
        ['open', 'accepted', 'active'].includes(c.status)
    );

    // Filter events for this group
    const groupEvents = props.upcomingEvents.filter(e => e.group.id === selectedGroupFilter.value);

    // Calculate win rate for this group
    const totalGroupWagers = groupWagers.length + groupSettled.length;
    const wonGroupWagers = groupSettled.filter(w => w.is_winner).length;
    const groupWinRate = totalGroupWagers > 0 ? Math.round((wonGroupWagers / totalGroupWagers) * 1000) / 10 : 0;

    return {
        total_balance: groupBalance,
        active_wagers: groupWagers.length,
        active_challenges: groupChallenges.length,
        upcoming_events: groupEvents.length,
        active_items: groupWagers.length + groupChallenges.length + groupEvents.length,
        win_rate: groupWinRate,
        total_wagers: totalGroupWagers,
        won_wagers: wonGroupWagers,
    };
});

// Selected group name for display
const selectedGroupName = computed(() => {
    if (selectedGroupFilter.value === 'all') return null;
    const group = props.groups.find(g => g.id === selectedGroupFilter.value);
    return group ? group.name : null;
});

// Computed properties for filtered data
const filteredOpenWagers = computed(() => {
    if (selectedGroupFilter.value === 'all') return props.openWagers;
    return props.openWagers.filter(w => w.group.id === selectedGroupFilter.value);
});

const filteredAwaitingSettlement = computed(() => {
    if (selectedGroupFilter.value === 'all') return props.awaitingSettlement;
    return props.awaitingSettlement.filter(w => w.group.id === selectedGroupFilter.value);
});

const filteredSettledWagers = computed(() => {
    if (selectedGroupFilter.value === 'all') return props.settledWagers;
    return props.settledWagers.filter(w => w.group.id === selectedGroupFilter.value);
});

const filteredChallenges = computed(() => {
    if (selectedGroupFilter.value === 'all') return props.userChallenges;
    return props.userChallenges.filter(c => c.group.id === selectedGroupFilter.value);
});

const filteredUpcomingEvents = computed(() => {
    if (selectedGroupFilter.value === 'all') return props.upcomingEvents;
    return props.upcomingEvents.filter(e => e.group.id === selectedGroupFilter.value);
});

const filteredPastUnprocessedEvents = computed(() => {
    if (selectedGroupFilter.value === 'all') return props.pastUnprocessedEvents;
    return props.pastUnprocessedEvents.filter(e => e.group.id === selectedGroupFilter.value);
});

const filteredPastProcessedEvents = computed(() => {
    if (selectedGroupFilter.value === 'all') return props.pastProcessedEvents;
    return props.pastProcessedEvents.filter(e => e.group.id === selectedGroupFilter.value);
});

const filteredTransactions = computed(() => {
    if (selectedGroupFilter.value === 'all') return props.recentTransactions;
    return props.recentTransactions.filter(t => t.group && t.group.id === selectedGroupFilter.value);
});

// Leaderboard: show when single group exists or specific group is selected
const showLeaderboard = computed(() => {
    return props.groups.length === 1 || selectedGroupFilter.value !== 'all';
});

// Get leaderboard data for the active group
const leaderboardData = computed(() => {
    if (!showLeaderboard.value) return [];

    // If only one group, use that; otherwise use selected group
    const groupId = props.groups.length === 1
        ? props.groups[0].id
        : selectedGroupFilter.value;

    const group = props.groups.find(g => g.id === groupId);
    return group?.leaderboard || [];
});

// Get the active group for leaderboard display
const leaderboardGroup = computed(() => {
    if (!showLeaderboard.value) return null;

    const groupId = props.groups.length === 1
        ? props.groups[0].id
        : selectedGroupFilter.value;

    return props.groups.find(g => g.id === groupId);
});

// Time formatting helpers
function formatTimeRemaining(deadlineStr: string): string {
    const deadline = new Date(deadlineStr);
    const diff = deadline.getTime() - Date.now();

    if (diff < 0) return 'Deadline passed';

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    if (days > 0) return `${days}d ${hours}h`;
    if (hours > 0) return `${hours}h ${mins}m`;
    return `${mins}m`;
}

function formatRelativeTime(dateStr: string): string {
    const date = new Date(dateStr);
    const diff = Date.now() - date.getTime();

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    if (days > 0) return `${days}d ago`;
    if (hours > 0) return `${hours}h ago`;
    if (mins > 0) return `${mins}m ago`;
    return 'Just now';
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Format transaction type to human-readable title
function formatTransactionType(type: string): string {
    const typeMap: Record<string, string> = {
        'wager_placed': 'Wager placed',
        'wager_won': 'Won wager',
        'wager_lost': 'Lost wager',
        'wager_refunded': 'Wager refunded',
        'point_decay': 'Point decay',
        'admin_adjustment': 'Admin adjustment',
        'initial_balance': 'Initial balance',
        'event_attendance_bonus': 'Event attendance bonus',
        'challenge_hold': 'Challenge stake held',
        'challenge_completed': 'Challenge completed',
        'challenge_failed': 'Challenge failed',
        'challenge_cancelled': 'Challenge cancelled',
        'drop': 'Drop received',
        'donation_sent': 'Donation sent',
        'donation_received': 'Donation received',
        'super_challenge_acceptance_bonus': 'Super Challenge acceptance bonus',
        'super_challenge_prize': 'Super Challenge prize',
        'super_challenge_validation_bonus': 'Super Challenge validation bonus',
        'elimination_buy_in': 'Elimination buy-in',
        'elimination_buy_in_refund': 'Elimination buy-in refund',
        'elimination_prize': 'Elimination prize',
        'elimination_system_contribution': 'Elimination system contribution',
        'dispute_penalty_false_report': 'Dispute penalty (false report)',
        'dispute_penalty_honest_mistake': 'Dispute penalty (honest mistake)',
        'dispute_penalty_fraud': 'Dispute penalty (fraud)',
        'dispute_penalty_premature': 'Dispute penalty (premature settlement)',
        'dispute_refund': 'Dispute refund',
        'dispute_correction': 'Dispute correction',
    };
    return typeMap[type] || type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

// Check if transaction type is a refund type
function isRefundType(type: string): boolean {
    return ['wager_refunded', 'elimination_buy_in_refund', 'dispute_refund'].includes(type);
}
</script>

<template>
    <AppLayout title="My Dashboard">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Page Header with Group Selector -->
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                    Dashboard
                </h2>

                <!-- Group Selector Split Button -->
                <div v-if="groups.length > 0" class="relative flex items-center">
                    <!-- Dropdown Button -->
                    <button
                        @click="isGroupDropdownOpen = !isGroupDropdownOpen"
                        class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-l-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:ring-2 focus:ring-blue-500"
                        type="button"
                    >
                        {{ selectedGroup?.name || 'All Groups' }}
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- View/Edit Group Button -->
                    <button
                        @click="viewSelectedGroup"
                        :disabled="!selectedGroup"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border-l-0 border border-neutral-300 dark:border-neutral-600 rounded-r-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{ 'hover:bg-white dark:hover:bg-neutral-800': !selectedGroup }"
                        type="button"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        v-if="isGroupDropdownOpen"
                        class="absolute top-full right-0 mt-2 w-56 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg shadow-lg z-50"
                    >
                        <ul class="py-1">
                            <li>
                                <button
                                    @click="selectGroup(null)"
                                    class="w-full text-left px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                                    :class="{ 'bg-blue-50 dark:bg-blue-900/20': selectedGroupFilter === 'all' }"
                                >
                                    All Groups
                                </button>
                            </li>
                            <li v-for="group in groups" :key="group.id">
                                <button
                                    @click="selectGroup(group.id)"
                                    class="w-full text-left px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                                    :class="{ 'bg-blue-50 dark:bg-blue-900/20': selectedGroupFilter === group.id }"
                                >
                                    {{ group.name }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Clickable Stats Overview -->
            <div :class="['grid gap-4 mb-8', selectedGroup?.house_pot > 0 ? 'grid-cols-2 md:grid-cols-4' : 'grid-cols-3']">
                <button
                    @click="activeTab = 'transactions'"
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4 text-left hover:shadow-md transition-shadow"
                >
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">
                        {{ selectedGroupName ? `Balance in ${selectedGroupName}` : 'Total Balance' }}
                    </div>
                    <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ filteredStats.total_balance }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        {{ selectedGroupName ? 'points' : `across ${groups.length} group${groups.length > 1 ? 's' : ''}` }}
                    </div>
                </button>

                <div
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4 text-left"
                >
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Active Items</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ filteredStats.active_items }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 flex flex-wrap gap-x-2">
                        <span v-if="filteredStats.active_wagers" class="cursor-pointer hover:text-blue-500" @click="activeTab = 'wagers'">{{ filteredStats.active_wagers }} wager{{ filteredStats.active_wagers !== 1 ? 's' : '' }}</span>
                        <span v-if="filteredStats.active_challenges" class="cursor-pointer hover:text-blue-500" @click="activeTab = 'challenges'">{{ filteredStats.active_challenges }} challenge{{ filteredStats.active_challenges !== 1 ? 's' : '' }}</span>
                        <span v-if="filteredStats.upcoming_events" class="cursor-pointer hover:text-blue-500" @click="activeTab = 'events'">{{ filteredStats.upcoming_events }} event{{ filteredStats.upcoming_events !== 1 ? 's' : '' }}</span>
                        <span v-if="!filteredStats.active_items">none active</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4">
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Win Rate</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ filteredStats.win_rate }}%</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ filteredStats.won_wagers }}/{{ filteredStats.total_wagers }} won</div>
                </div>

                <!-- House Pot - only shown when a group is selected and has pot > 0 -->
                <div v-if="selectedGroup?.house_pot > 0" class="bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-lg shadow p-4 border border-amber-200 dark:border-amber-800">
                    <div class="text-sm text-amber-700 dark:text-amber-400 mb-1">House Pot</div>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ selectedGroup.house_pot }}</div>
                    <div class="text-xs text-amber-600/70 dark:text-amber-500/70 mt-1">{{ selectedGroup.currency }} unclaimed</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow">
                <!-- Mobile: Dropdown -->
                <div class="md:hidden border-b border-neutral-200 dark:border-neutral-700 p-4">
                    <select
                        v-model="activeTab"
                        class="w-full px-4 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="wagers">Wagers</option>
                        <option value="challenges">Challenges</option>
                        <option value="events">Events</option>
                        <option value="transactions">Transactions</option>
                        <option value="badges">Badges</option>
                        <option v-if="showLeaderboard" value="leaderboard">Leaderboard</option>
                    </select>
                </div>

                <!-- Desktop: Horizontal tabs -->
                <div class="hidden md:block border-b border-neutral-200 dark:border-neutral-700">
                    <nav class="flex -mb-px">
                        <button
                            @click="activeTab = 'wagers'"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2',
                                activeTab === 'wagers'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                            ]"
                        >
                            Wagers
                        </button>
                        <button
                            @click="activeTab = 'challenges'"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2',
                                activeTab === 'challenges'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                            ]"
                        >
                            Challenges
                        </button>
                        <button
                            @click="activeTab = 'events'"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2',
                                activeTab === 'events'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                            ]"
                        >
                            Events
                        </button>
                        <button
                            @click="activeTab = 'transactions'"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2',
                                activeTab === 'transactions'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                            ]"
                        >
                            Transactions
                        </button>
                        <button
                            @click="activeTab = 'badges'"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2',
                                activeTab === 'badges'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                            ]"
                        >
                            Badges
                            <span v-if="userBadges.length > 0" class="ml-1 text-xs bg-neutral-200 dark:bg-neutral-600 px-1.5 py-0.5 rounded-full">
                                {{ userBadges.length }}
                            </span>
                        </button>
                        <button
                            v-if="showLeaderboard"
                            @click="activeTab = 'leaderboard'"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2',
                                activeTab === 'leaderboard'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                            ]"
                        >
                            Leaderboard
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Wagers Tab -->
                    <div v-if="activeTab === 'wagers'">
                        <!-- Open Wagers (Before Deadline) -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Open ({{ filteredOpenWagers.length }})</h3>
                            <div v-if="filteredOpenWagers.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No open wagers
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="wager in filteredOpenWagers" :key="wager.id" class="bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="wager.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ wager.title }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ wager.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ formatTimeRemaining(wager.betting_closes_at) }}</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ wager.participants_count }} participants</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                            <span v-if="wager.is_creator" class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded text-xs">Creator</span>
                                            <span v-else class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded text-xs">Your answer: {{ wager.user_answer }}</span>
                                            <span class="ml-2 text-neutral-600 dark:text-neutral-400">{{ wager.user_points_wagered }} {{ wager.group.currency }} wagered</span>
                                        </div>
                                        <a :href="wager.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Awaiting Settlement (Past Deadline) -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Awaiting Settlement ({{ filteredAwaitingSettlement.length }})</h3>
                            <div v-if="filteredAwaitingSettlement.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No wagers awaiting settlement
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="wager in filteredAwaitingSettlement" :key="wager.id" class="bg-amber-50 dark:bg-amber-900/20 rounded p-4 border border-amber-200 dark:border-amber-800">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="wager.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ wager.title }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ wager.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-amber-600 dark:text-amber-400">Deadline passed</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ wager.participants_count }} participants</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                            <span v-if="wager.is_creator" class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded text-xs">Creator</span>
                                            <span v-else class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded text-xs">Your answer: {{ wager.user_answer }}</span>
                                            <span class="ml-2 text-neutral-600 dark:text-neutral-400">{{ wager.user_points_wagered }} {{ wager.group.currency }} wagered</span>
                                        </div>
                                        <a :href="wager.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settled Wagers -->
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Settled</h3>
                            <div v-if="filteredSettledWagers.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No settled wagers yet
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="wager in filteredSettledWagers" :key="wager.id" class="bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="wager.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ wager.title }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ wager.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatRelativeTime(wager.settled_at) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm flex items-center gap-2 flex-wrap">
                                            <template v-if="wager.status === 'disputed'">
                                                <DisputeStatusBadge
                                                    status="disputed"
                                                    :dispute-id="wager.dispute?.id"
                                                    size="sm"
                                                    clickable
                                                />
                                                <span class="text-neutral-500 dark:text-neutral-400">Outcome pending</span>
                                            </template>
                                            <template v-else>
                                                <span v-if="wager.result === 'refunded'" class="text-blue-600 dark:text-blue-400 font-medium">Refunded {{ wager.user_points_wagered }} {{ wager.group.currency }}</span>
                                                <span v-else-if="wager.is_winner" class="text-green-600 dark:text-green-400 font-medium">Won {{ wager.points_won }} {{ wager.group.currency }}</span>
                                                <span v-else class="text-red-600 dark:text-red-400">Lost {{ wager.user_points_wagered }} {{ wager.group.currency }}</span>
                                                <span class="text-neutral-600 dark:text-neutral-400">Outcome: {{ wager.outcome_value }}</span>
                                            </template>
                                        </div>
                                        <a :href="wager.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Challenges Tab -->
                    <div v-if="activeTab === 'challenges'">
                        <!-- Active Elimination Challenges (user is participating and not eliminated) -->
                        <div v-if="filteredChallenges.filter(c => c.type === 'elimination_challenge' && c.status === 'active').length > 0" class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">
                                🎯 Active Eliminations ({{ filteredChallenges.filter(c => c.type === 'elimination_challenge' && c.status === 'active').length }})
                            </h3>
                            <div class="space-y-3">
                                <div v-for="challenge in filteredChallenges.filter(c => c.type === 'elimination_challenge' && c.status === 'active')" :key="challenge.id" class="bg-orange-50 dark:bg-orange-900/20 rounded p-4 border border-orange-200 dark:border-orange-800">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="challenge.url" class="font-medium text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 hover:underline">{{ challenge.description }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ challenge.group.name }}</div>
                                            <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">⚡ {{ challenge.elimination_trigger }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ challenge.amount?.toLocaleString() }} pts pot</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ challenge.survivors_count }}/{{ challenge.participants_count }} surviving</div>
                                            <div v-if="challenge.completion_deadline" class="text-xs text-neutral-500 dark:text-neutral-400">Ends: {{ formatDate(challenge.completion_deadline) }}</div>
                                            <div v-else class="text-xs text-neutral-500 dark:text-neutral-400">Last one standing</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300 flex items-center gap-2">
                                            <span class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-0.5 rounded text-xs font-medium">🎯 Elimination</span>
                                            <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-0.5 rounded text-xs">✓ You're in!</span>
                                            <span v-if="challenge.is_creator" class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded text-xs">Creator</span>
                                        </div>
                                        <a :href="challenge.url" class="text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Open Challenges (including elimination challenges available to tap in) -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Open ({{ filteredChallenges.filter(c => c.status === 'open').length }})</h3>
                            <div v-if="filteredChallenges.filter(c => c.status === 'open').length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No open challenges
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="challenge in filteredChallenges.filter(c => c.status === 'open')" :key="challenge.id"
                                     :class="challenge.type === 'elimination_challenge'
                                         ? 'bg-orange-50 dark:bg-orange-900/20 rounded p-4 border border-orange-200 dark:border-orange-800'
                                         : 'bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600'">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="challenge.url" :class="challenge.type === 'elimination_challenge'
                                                    ? 'font-medium text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 hover:underline'
                                                    : 'font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline'">
                                                {{ challenge.description }}
                                            </a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ challenge.group.name }}</div>
                                            <div v-if="challenge.type === 'elimination_challenge'" class="text-xs text-orange-600 dark:text-orange-400 mt-1">⚡ {{ challenge.elimination_trigger }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div v-if="challenge.type === 'elimination_challenge'" class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ challenge.amount?.toLocaleString() }} pts pot</div>
                                            <div v-else class="text-sm font-medium text-green-600 dark:text-green-400">{{ Math.abs(challenge.amount) }} pts</div>
                                            <div v-if="challenge.type === 'elimination_challenge' && challenge.buy_in_amount" class="text-xs text-neutral-500 dark:text-neutral-400">Buy-in: {{ challenge.buy_in_amount }} pts</div>
                                            <div v-if="challenge.type === 'elimination_challenge' && challenge.tap_in_deadline" class="text-xs text-neutral-500 dark:text-neutral-400">Tap in by: {{ formatDate(challenge.tap_in_deadline) }}</div>
                                            <div v-else-if="challenge.acceptance_deadline" class="text-xs text-neutral-500 dark:text-neutral-400">Accept by: {{ formatDate(challenge.acceptance_deadline) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300 flex items-center gap-2">
                                            <span v-if="challenge.type === 'elimination_challenge'" class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-0.5 rounded text-xs font-medium">🎯 Elimination</span>
                                            <span v-else-if="challenge.type === 'super_challenge'" class="bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded text-xs font-medium">⭐ SuperChallenge</span>
                                            <span v-if="challenge.is_creator" class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded text-xs">Creator</span>
                                            <span v-else-if="challenge.type === 'elimination_challenge'" class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-0.5 rounded text-xs">Tap in to join!</span>
                                            <span v-else class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-0.5 rounded text-xs">Available to accept</span>
                                            <span v-if="challenge.type === 'elimination_challenge' && challenge.participants_count" class="text-neutral-600 dark:text-neutral-400">{{ challenge.participants_count }} joined</span>
                                        </div>
                                        <a :href="challenge.url" :class="challenge.type === 'elimination_challenge'
                                                ? 'text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 text-sm'
                                                : 'text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm'">
                                            View →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accepted Challenges (non-elimination) -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Accepted ({{ filteredChallenges.filter(c => c.status === 'accepted').length }})</h3>
                            <div v-if="filteredChallenges.filter(c => c.status === 'accepted').length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No accepted challenges
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="challenge in filteredChallenges.filter(c => c.status === 'accepted')" :key="challenge.id" class="bg-blue-50 dark:bg-blue-900/20 rounded p-4 border border-blue-200 dark:border-blue-800">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="challenge.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ challenge.description }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ challenge.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div v-if="challenge.completion_deadline" class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ formatTimeRemaining(challenge.completion_deadline) }}</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ Math.abs(challenge.amount) }} pts reward</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300 flex items-center gap-2">
                                            <span v-if="challenge.type === 'super_challenge'" class="bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded text-xs font-medium">⭐ SuperChallenge</span>
                                            <span v-if="challenge.is_creator" class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded text-xs">Creator</span>
                                            <span v-else class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded text-xs">Accepted by you</span>
                                            <span v-if="challenge.acceptor" class="ml-2 text-neutral-600 dark:text-neutral-400">Acceptor: {{ challenge.acceptor.name }}</span>
                                        </div>
                                        <a :href="challenge.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Eliminated (user was eliminated from elimination challenges) -->
                        <div v-if="filteredChallenges.filter(c => c.type === 'elimination_challenge' && c.status === 'eliminated').length > 0" class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">
                                💀 Eliminated ({{ filteredChallenges.filter(c => c.type === 'elimination_challenge' && c.status === 'eliminated').length }})
                            </h3>
                            <div class="space-y-3">
                                <div v-for="challenge in filteredChallenges.filter(c => c.type === 'elimination_challenge' && c.status === 'eliminated')" :key="challenge.id" class="bg-red-50 dark:bg-red-900/20 rounded p-4 border border-red-200 dark:border-red-800 opacity-75">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="challenge.url" class="font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:underline">{{ challenge.description }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ challenge.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-red-600 dark:text-red-400">{{ challenge.amount?.toLocaleString() }} pts pot</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ challenge.survivors_count }} still surviving</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300 flex items-center gap-2">
                                            <span class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-2 py-0.5 rounded text-xs font-medium">💀 Eliminated</span>
                                            <span class="text-red-600 dark:text-red-400">-{{ challenge.buy_in_amount }} pts</span>
                                        </div>
                                        <a :href="challenge.url" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Completed Challenges -->
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Completed</h3>
                            <div v-if="filteredChallenges.filter(c => ['completed', 'failed'].includes(c.status)).length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No completed challenges yet
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="challenge in filteredChallenges.filter(c => ['completed', 'failed'].includes(c.status))" :key="challenge.id" class="bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="challenge.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ challenge.description }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ challenge.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatRelativeTime(challenge.completed_at || challenge.failed_at) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm flex items-center gap-2">
                                            <span v-if="challenge.type === 'elimination_challenge'" class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-0.5 rounded text-xs font-medium">🎯 Elimination</span>
                                            <span v-else-if="challenge.type === 'super_challenge'" class="bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded text-xs font-medium">⭐ SuperChallenge</span>
                                            <span v-if="challenge.status === 'completed'" class="text-green-600 dark:text-green-400 font-medium">✅ Completed (+{{ Math.abs(challenge.amount) }} pts)</span>
                                            <span v-else class="text-red-600 dark:text-red-400">❌ Failed</span>
                                            <span v-if="challenge.acceptor" class="ml-2 text-neutral-600 dark:text-neutral-400">by {{ challenge.acceptor.name }}</span>
                                        </div>
                                        <a :href="challenge.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Events Tab -->
                    <div v-if="activeTab === 'events'">
                        <!-- Upcoming Events -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Upcoming ({{ filteredUpcomingEvents.length }})</h3>
                            <div v-if="filteredUpcomingEvents.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No upcoming events
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="event in filteredUpcomingEvents" :key="event.id" class="bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="event.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ event.name }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ event.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ formatDate(event.event_date) }}</div>
                                            <div v-if="event.location" class="text-xs text-neutral-500 dark:text-neutral-400">📍 {{ event.location }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                            <span v-if="event.rsvp_enabled" class="text-neutral-600 dark:text-neutral-400">
                                                {{ event.rsvps.going }} going · {{ event.rsvps.maybe }} maybe
                                            </span>
                                            <span class="ml-2 text-green-600 dark:text-green-400">{{ event.attendance_bonus }} pts</span>
                                        </div>
                                        <a :href="event.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Past Unprocessed Events -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Past (Unprocessed) ({{ filteredPastUnprocessedEvents.length }})</h3>
                            <div v-if="filteredPastUnprocessedEvents.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No unprocessed events
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="event in filteredPastUnprocessedEvents" :key="event.id" class="bg-amber-50 dark:bg-amber-900/20 rounded p-4 border border-amber-200 dark:border-amber-800">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="event.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ event.name }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ event.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-amber-600 dark:text-amber-400">Needs attendance</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(event.event_date) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                            <span class="text-amber-600 dark:text-amber-400">⚠️ Record attendance to award bonuses</span>
                                        </div>
                                        <a :href="event.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Past Processed Events -->
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Past (Completed)</h3>
                            <div v-if="filteredPastProcessedEvents.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No completed events yet
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="event in filteredPastProcessedEvents" :key="event.id" class="bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="event.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ event.name }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ event.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(event.event_date) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm">
                                            <span class="text-green-600 dark:text-green-400">✅ {{ event.attendance_count }} attended</span>
                                            <span v-if="event.user_attended" class="ml-2 text-blue-600 dark:text-blue-400">(You attended: +{{ event.attendance_bonus }} pts)</span>
                                        </div>
                                        <a :href="event.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Tab -->
                    <div v-if="activeTab === 'transactions'">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Recent Transactions</h3>
                        <div v-if="filteredTransactions.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                            No transactions yet
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="tx in filteredTransactions" :key="tx.id" class="flex justify-between items-center bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ tx.description || formatTransactionType(tx.type) }}</div>
                                        <span v-if="isRefundType(tx.type)" class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">REFUND</span>
                                    </div>
                                    <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                        <span v-if="tx.group">{{ tx.group.name }}</span>
                                        <span v-if="tx.wager"> · {{ tx.wager.title }}</span>
                                    </div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ formatDate(tx.created_at) }}</div>
                                </div>
                                <div class="text-right">
                                    <div :class="[
                                        'font-bold',
                                        isRefundType(tx.type) ? 'text-blue-600 dark:text-blue-400' :
                                        tx.amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                                    ]">
                                        {{ tx.amount >= 0 ? '+' : '' }}{{ tx.amount }} {{ tx.currency || 'points' }}
                                    </div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                        Balance: {{ tx.balance_after }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Badges Tab -->
                    <div v-if="activeTab === 'badges'">
                        <BadgeCollection
                            :badges="userBadges"
                            title="Your Badges"
                            show-empty
                            show-group
                        />
                    </div>

                    <!-- Leaderboard Tab -->
                    <div v-if="activeTab === 'leaderboard' && showLeaderboard">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">
                            {{ leaderboardGroup?.name }} Leaderboard
                        </h3>
                        <div v-if="leaderboardData.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                            No members found
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="member in leaderboardData"
                                :key="member.id"
                                class="flex justify-between items-center bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600"
                                :class="{ 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': member.id === user.id }"
                            >
                                <div class="flex items-center gap-3">
                                    <!-- Rank badge -->
                                    <div
                                        class="w-8 h-8 flex items-center justify-center rounded-full font-bold text-sm"
                                        :class="{
                                            'bg-yellow-400 text-yellow-900': member.rank === 1,
                                            'bg-gray-300 text-gray-700': member.rank === 2,
                                            'bg-amber-600 text-white': member.rank === 3,
                                            'bg-neutral-200 dark:bg-neutral-600 text-neutral-700 dark:text-neutral-300': member.rank > 3
                                        }"
                                    >
                                        {{ member.rank }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                            {{ member.name }}
                                            <span v-if="member.id === user.id" class="text-xs text-blue-600 dark:text-blue-400 ml-1">(You)</span>
                                        </div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                            Won: {{ member.points_earned }} · Spent: {{ member.points_spent }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                                        {{ member.points }}
                                    </div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ leaderboardGroup?.currency || 'points' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
