<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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
    focus: string;
}>();

// Active tab management - default based on focus or wagers if not set
const getDefaultTab = () => {
    if (props.focus === 'transactions') return 'transactions';
    if (props.focus === 'events') return 'events';
    if (props.focus === 'challenges') return 'challenges';
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

    // Calculate win rate for this group
    const totalGroupWagers = groupWagers.length + groupSettled.length;
    const wonGroupWagers = groupSettled.filter(w => w.is_winner).length;
    const groupWinRate = totalGroupWagers > 0 ? Math.round((wonGroupWagers / totalGroupWagers) * 1000) / 10 : 0;

    return {
        total_balance: groupBalance,
        active_wagers: groupWagers.length,
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
            <div class="grid grid-cols-3 gap-4 mb-8">
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

                <button
                    @click="activeTab = 'wagers'"
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4 text-left hover:shadow-md transition-shadow"
                >
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Active Wagers</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ filteredStats.active_wagers }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">in progress</div>
                </button>

                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4">
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Win Rate</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ filteredStats.win_rate }}%</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ filteredStats.won_wagers }}/{{ filteredStats.total_wagers }} won</div>
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
                                            <span class="ml-2 text-neutral-600 dark:text-neutral-400">{{ wager.user_points_wagered }} pts wagered</span>
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
                                            <span class="ml-2 text-neutral-600 dark:text-neutral-400">{{ wager.user_points_wagered }} pts wagered</span>
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
                                        <div class="text-sm">
                                            <span v-if="wager.is_winner" class="text-green-600 dark:text-green-400 font-medium">Won {{ wager.payout_amount }} pts</span>
                                            <span v-else class="text-red-600 dark:text-red-400">Lost {{ wager.user_points_wagered }} pts</span>
                                            <span class="ml-2 text-neutral-600 dark:text-neutral-400">Outcome: {{ wager.outcome_value }}</span>
                                        </div>
                                        <a :href="wager.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Challenges Tab -->
                    <div v-if="activeTab === 'challenges'">
                        <!-- Open Challenges -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Open ({{ filteredChallenges.filter(c => c.status === 'open').length }})</h3>
                            <div v-if="filteredChallenges.filter(c => c.status === 'open').length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No open challenges
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="challenge in filteredChallenges.filter(c => c.status === 'open')" :key="challenge.id" class="bg-neutral-50 dark:bg-neutral-700 rounded p-4 border border-neutral-200 dark:border-neutral-600">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <a :href="challenge.url" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ challenge.description }}</a>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ challenge.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-green-600 dark:text-green-400">{{ Math.abs(challenge.amount) }} pts</div>
                                            <div v-if="challenge.acceptance_deadline" class="text-xs text-neutral-500 dark:text-neutral-400">Accept by: {{ formatDate(challenge.acceptance_deadline) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                            <span v-if="challenge.is_creator" class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded text-xs">Creator</span>
                                            <span v-else class="bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 px-2 py-0.5 rounded text-xs">Available to accept</span>
                                        </div>
                                        <a :href="challenge.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accepted Challenges -->
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
                                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ formatTimeRemaining(challenge.completion_deadline) }}</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ Math.abs(challenge.amount) }} pts reward</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                            <span v-if="challenge.is_creator" class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-0.5 rounded text-xs">Creator</span>
                                            <span v-else class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded text-xs">Accepted by you</span>
                                            <span v-if="challenge.acceptor" class="ml-2 text-neutral-600 dark:text-neutral-400">Acceptor: {{ challenge.acceptor.name }}</span>
                                        </div>
                                        <a :href="challenge.url" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">View →</a>
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
                                        <div class="text-sm">
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
                                        <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ tx.description || tx.type }}</div>
                                        <span v-if="tx.type === 'wager_refunded'" class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">REFUND</span>
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
                                        tx.type === 'wager_refunded' ? 'text-blue-600 dark:text-blue-400' :
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
                </div>
            </div>
        </div>
    </AppLayout>
</template>
