<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    challenge: {
        id: string;
        description: string;
        elimination_trigger: string;
        elimination_mode: 'last_man_standing' | 'deadline';
        status: string;
        point_pot: number;
        buy_in_amount: number;
        tap_in_deadline: string | null;
        completion_deadline: string | null;
        min_participants: number;
        created_at: string;
        completed_at: string | null;
        cancelled_at: string | null;
    };
    creator: {
        id: string;
        name: string;
    };
    group: {
        id: string;
        name: string;
        currency: string;
    };
    participants: Array<{
        id: string;
        user_name: string;
        is_eliminated: boolean;
        eliminated_at: string | null;
        days_survived: number;
    }>;
    userParticipant: {
        id: string;
        is_eliminated: boolean;
        days_survived: number;
    } | null;
    isCreator: boolean;
    canTapIn: boolean;
    canTapOut: boolean;
    canCancel: boolean;
}>();

const now = ref(Date.now());

// Update countdown every second
const intervalId = setInterval(() => { now.value = Date.now(); }, 1000);

// Clean up interval when component unmounts
onUnmounted(() => {
    clearInterval(intervalId);
});

// Computed properties
const survivors = computed(() => props.participants.filter(p => !p.is_eliminated));
const eliminated = computed(() => props.participants.filter(p => p.is_eliminated));

const potPerSurvivor = computed(() => {
    if (survivors.value.length === 0) return 0;
    return Math.floor(props.challenge.point_pot / survivors.value.length);
});

const eliminationModeText = computed(() => {
    return props.challenge.elimination_mode === 'last_man_standing'
        ? 'Last Man Standing'
        : 'Deadline Mode';
});

const statusColor = computed(() => {
    switch (props.challenge.status) {
        case 'open': return 'text-blue-600 dark:text-blue-400';
        case 'in_progress': return 'text-amber-600 dark:text-amber-400';
        case 'completed': return 'text-green-600 dark:text-green-400';
        case 'cancelled': return 'text-neutral-600 dark:text-neutral-400';
        default: return 'text-neutral-600 dark:text-neutral-400';
    }
});

const statusIcon = computed(() => {
    switch (props.challenge.status) {
        case 'open': return '🔓';
        case 'in_progress': return '⚡';
        case 'completed': return '🏆';
        case 'cancelled': return '🚫';
        default: return '❓';
    }
});

const statusText = computed(() => {
    switch (props.challenge.status) {
        case 'open': return 'Open for Tap-In';
        case 'in_progress': return 'In Progress';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        default: return 'Unknown';
    }
});

// Time remaining calculations
const tapInDeadline = props.challenge.tap_in_deadline ? new Date(props.challenge.tap_in_deadline) : null;
const completionDeadline = props.challenge.completion_deadline ? new Date(props.challenge.completion_deadline) : null;

const tapInTimeRemaining = computed(() => {
    if (!tapInDeadline) return null;
    const diff = tapInDeadline.getTime() - now.value;
    if (diff < 0) return 'Tap-in closed';

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    if (days > 0) return `${days}d ${hours}h ${mins}m to tap in`;
    if (hours > 0) return `${hours}h ${mins}m to tap in`;
    return `${mins}m to tap in`;
});

const completionTimeRemaining = computed(() => {
    if (!completionDeadline) return null;
    const diff = completionDeadline.getTime() - now.value;
    if (diff < 0) return 'Challenge ended';

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const secs = Math.floor((diff % (1000 * 60)) / 1000);

    if (days > 0) return `${days}d ${hours}h ${mins}m remaining`;
    if (hours > 0) return `${hours}h ${mins}m ${secs}s remaining`;
    return `${mins}m ${secs}s remaining`;
});

// Format date
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Cancel form
const cancelForm = useForm({});
const showCancelModal = ref(false);

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

const cancelChallenge = () => {
    showToast.value = false;

    cancelForm.post(`/elimination/${props.challenge.id}/cancel`, {
        onSuccess: () => {
            // Will redirect to cancelled page
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to cancel challenge';
            showToast.value = true;
            showCancelModal.value = false;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Elimination Challenge" />

        <div class="max-w-4xl mx-auto py-12 px-4">
            <!-- Header Card -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">
                            {{ challenge.description }}
                        </h1>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            Created by <strong>{{ creator.name }}</strong> in <strong>{{ group.name }}</strong>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xl font-bold" :class="statusColor">
                        <span>{{ statusIcon }}</span>
                        <span>{{ statusText }}</span>
                    </div>
                </div>

                <!-- Elimination Trigger -->
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-700 dark:text-red-300">
                        <span class="font-medium">Elimination Trigger:</span> {{ challenge.elimination_trigger }}
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Prize Pot</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">
                            {{ challenge.point_pot }} {{ group.currency }}
                        </p>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Survivors</p>
                        <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                            {{ survivors.length }}
                        </p>
                    </div>
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-center">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Eliminated</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">
                            {{ eliminated.length }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-center">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Per Survivor</p>
                        <p class="text-xl font-bold text-purple-600 dark:text-purple-400">
                            {{ potPerSurvivor }} {{ group.currency }}
                        </p>
                    </div>
                </div>

                <!-- Deadlines -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div v-if="tapInDeadline && challenge.status === 'open'" class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Tap-In Deadline</p>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ tapInTimeRemaining }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(challenge.tap_in_deadline) }}</p>
                    </div>
                    <div v-if="completionDeadline" class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Challenge Ends</p>
                        <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ completionTimeRemaining }}</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatDate(challenge.completion_deadline) }}</p>
                    </div>
                </div>

                <!-- Challenge Info -->
                <div class="text-sm text-neutral-600 dark:text-neutral-400 space-y-1">
                    <p><span class="font-medium">Mode:</span> {{ eliminationModeText }}</p>
                    <p><span class="font-medium">Buy-in:</span> {{ challenge.buy_in_amount }} {{ group.currency }}</p>
                    <p><span class="font-medium">Min Participants:</span> {{ challenge.min_participants }}</p>
                </div>
            </div>

            <!-- User Status & Actions -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">Your Status</h2>

                <div v-if="userParticipant" class="mb-4">
                    <div v-if="userParticipant.is_eliminated" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="font-medium text-red-700 dark:text-red-300">
                            You've been eliminated
                        </p>
                        <p class="text-sm text-red-600 dark:text-red-400">
                            You survived {{ userParticipant.days_survived }} {{ userParticipant.days_survived === 1 ? 'day' : 'days' }}
                        </p>
                    </div>
                    <div v-else class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="font-medium text-green-700 dark:text-green-300">
                            You're still in the game!
                        </p>
                        <p class="text-sm text-green-600 dark:text-green-400">
                            {{ userParticipant.days_survived }} {{ userParticipant.days_survived === 1 ? 'day' : 'days' }} survived
                        </p>
                    </div>
                </div>
                <div v-else class="mb-4 p-4 bg-neutral-100 dark:bg-neutral-700 rounded-lg">
                    <p class="text-neutral-600 dark:text-neutral-300">
                        You're not participating in this challenge
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <Link
                        v-if="canTapIn"
                        :href="`/elimination/${challenge.id}/tap-in`"
                        class="block w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 font-semibold text-center"
                    >
                        Tap In ({{ challenge.buy_in_amount }} {{ group.currency }})
                    </Link>

                    <Link
                        v-if="canTapOut"
                        :href="`/elimination/${challenge.id}/tap-out`"
                        class="block w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold text-center"
                    >
                        Tap Out
                    </Link>

                    <button
                        v-if="canCancel"
                        @click="showCancelModal = true"
                        class="w-full px-6 py-3 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 font-semibold"
                    >
                        Cancel Challenge
                    </button>
                </div>
            </div>

            <!-- Participants List -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">
                    Participants ({{ participants.length }})
                </h2>

                <!-- Survivors -->
                <div v-if="survivors.length > 0" class="mb-6">
                    <h3 class="text-sm font-medium text-green-600 dark:text-green-400 mb-3">
                        Survivors ({{ survivors.length }})
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="participant in survivors"
                            :key="participant.id"
                            class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-lg">💪</span>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ participant.user_name }}</span>
                            </div>
                            <span class="text-sm text-green-600 dark:text-green-400">
                                {{ participant.days_survived }}d survived
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Eliminated -->
                <div v-if="eliminated.length > 0">
                    <h3 class="text-sm font-medium text-red-600 dark:text-red-400 mb-3">
                        Eliminated ({{ eliminated.length }})
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="participant in eliminated"
                            :key="participant.id"
                            class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-lg">💀</span>
                                <span class="font-medium text-neutral-900 dark:text-white line-through opacity-75">{{ participant.user_name }}</span>
                            </div>
                            <div class="text-right text-sm">
                                <span class="text-red-600 dark:text-red-400">{{ participant.days_survived }}d</span>
                                <span class="text-neutral-500 dark:text-neutral-400 text-xs block">
                                    {{ formatDate(participant.eliminated_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Participants -->
                <div v-if="participants.length === 0" class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                    No participants yet. Be the first to tap in!
                </div>
            </div>

            <!-- Back to Dashboard -->
            <div class="mt-6 text-center">
                <a href="/me" class="text-blue-600 dark:text-blue-400 hover:underline">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">
                    Cancel Challenge
                </h3>
                <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                    Are you sure you want to cancel this elimination challenge?
                </p>
                <p class="text-sm text-amber-600 dark:text-amber-400 mb-6">
                    All buy-ins will be refunded to participants.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showCancelModal = false"
                        class="px-4 py-2 bg-neutral-300 dark:bg-neutral-600 text-neutral-700 dark:text-neutral-300 rounded-md hover:bg-neutral-400 dark:hover:bg-neutral-500"
                    >
                        Keep Challenge
                    </button>
                    <button
                        @click="cancelChallenge"
                        :disabled="cancelForm.processing"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ cancelForm.processing ? 'Cancelling...' : 'Cancel Challenge' }}
                    </button>
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
