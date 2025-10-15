<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    user: any;
    stats: any;
    groups: any[];
    activeWagers: any[];
    openWagers: any[];
    awaitingSettlement: any[];
    settledWagers: any[];
    recentTransactions: any[];
    focus: string;
}>();

// Active tab management - default based on focus or wagers if not set
const getDefaultTab = () => {
    if (props.focus === 'transactions') return 'transactions';
    if (props.focus === 'groups') return 'groups';
    return 'wagers';
};

const activeTab = ref(getDefaultTab());

// Profile edit form (session-based auth, no token needed)
const profileForm = useForm({
    taunt_line: props.user.taunt_line || '',
    birthday: props.user.birthday || '',
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

function submitProfile() {
    // Clear previous toasts
    showToast.value = false;

    profileForm.post('/me/profile', {
        preserveScroll: true,
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Profile updated successfully!';
            showToast.value = true;
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to update profile. Please try again.';
            showToast.value = true;
        },
    });
}

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
            <!-- Page Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                    Dashboard
                </h2>
            </div>

            <!-- Clickable Stats Overview -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <button
                    @click="activeTab = 'transactions'"
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4 text-left hover:shadow-md transition-shadow"
                >
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Total Balance</div>
                    <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ stats.total_balance }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">points</div>
                </button>

                <button
                    @click="activeTab = 'wagers'"
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4 text-left hover:shadow-md transition-shadow"
                >
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Active Wagers</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.active_wagers }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">in progress</div>
                </button>

                <button
                    @click="activeTab = 'groups'"
                    class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4 text-left hover:shadow-md transition-shadow"
                >
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Groups</div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ groups.length }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">participating</div>
                </button>

                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow p-4">
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-1">Win Rate</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.win_rate }}%</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ stats.won_wagers }}/{{ stats.total_wagers }} won</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow">
                <div class="border-b border-neutral-200 dark:border-neutral-700">
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
                            My Wagers
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
                            @click="activeTab = 'groups'"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2',
                                activeTab === 'groups'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                            ]"
                        >
                            Groups
                        </button>
                        <button
                            @click="activeTab = 'profile'"
                            :class="[
                                'px-6 py-3 text-sm font-medium border-b-2',
                                activeTab === 'profile'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                            ]"
                        >
                            Profile
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Wagers Tab -->
                    <div v-if="activeTab === 'wagers'">
                        <!-- Open Wagers (Before Deadline) -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Open ({{ openWagers.length }})</h3>
                            <div v-if="openWagers.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No open wagers
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="wager in openWagers" :key="wager.id" class="bg-neutral-50 dark:bg-neutral-700 rounded p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ wager.title }}</div>
                                            <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ wager.group.name }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ formatTimeRemaining(wager.deadline) }}</div>
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
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Awaiting Settlement ({{ awaitingSettlement.length }})</h3>
                            <div v-if="awaitingSettlement.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No wagers awaiting settlement
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="wager in awaitingSettlement" :key="wager.id" class="bg-amber-50 dark:bg-amber-900/20 rounded p-4 border border-amber-200 dark:border-amber-800">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ wager.title }}</div>
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
                            <div v-if="settledWagers.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                                No settled wagers yet
                            </div>
                            <div v-else class="space-y-3">
                                <div v-for="wager in settledWagers" :key="wager.id" class="bg-neutral-50 dark:bg-neutral-700 rounded p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ wager.title }}</div>
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

                    <!-- Transactions Tab -->
                    <div v-if="activeTab === 'transactions'">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Recent Transactions</h3>
                        <div v-if="recentTransactions.length === 0" class="text-neutral-500 dark:text-neutral-400 text-center py-8">
                            No transactions yet
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="tx in recentTransactions" :key="tx.id" class="flex justify-between items-center bg-neutral-50 dark:bg-neutral-700 rounded p-3">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-neutral-900 dark:text-neutral-100">{{ tx.description || tx.type }}</div>
                                    <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                        <span v-if="tx.group">{{ tx.group.name }}</span>
                                        <span v-if="tx.wager"> · {{ tx.wager.title }}</span>
                                    </div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ formatDate(tx.created_at) }}</div>
                                </div>
                                <div class="text-right">
                                    <div :class="['font-bold', tx.amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                                        {{ tx.amount >= 0 ? '+' : '' }}{{ tx.amount }}
                                    </div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ tx.balance_before }} → {{ tx.balance_after }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Groups Tab -->
                    <div v-if="activeTab === 'groups'">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Your Groups</h3>
                        <div class="space-y-2">
                            <div v-for="group in groups" :key="group.id" class="flex justify-between items-center bg-neutral-50 dark:bg-neutral-700 rounded p-4">
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ group.name }}</div>
                                    <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ group.role }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-lg text-neutral-900 dark:text-neutral-100">{{ group.balance }}</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">points</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Tab -->
                    <div v-if="activeTab === 'profile'">
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Profile Settings</h3>

                        <form @submit.prevent="submitProfile" class="space-y-6 max-w-lg">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Taunt Line
                                </label>
                                <input
                                    v-model="profileForm.taunt_line"
                                    type="text"
                                    maxlength="255"
                                    class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Your victory message to others..."
                                />
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    This message will be displayed when you win a wager
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Birthday
                                </label>
                                <input
                                    v-model="profileForm.birthday"
                                    type="date"
                                    class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    The bot will send birthday wishes in your groups
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2"
                                >
                                    <!-- Loading spinner -->
                                    <svg v-if="profileForm.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ profileForm.processing ? 'Saving...' : 'Save Profile' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <Toast
            :show="showToast"
            :type="toastType"
            :message="toastMessage"
            @close="showToast = false"
        />
    </AppLayout>
</template>
