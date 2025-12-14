<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';
import FormError from '@/Components/FormError.vue';

interface Vote {
    id: string;
    voter_name: string;
    vote_outcome: string;
    vote_outcome_label: string;
    selected_outcome: string | null;
    created_at: string;
}

const props = defineProps<{
    dispute: {
        id: string;
        status: 'pending' | 'resolved';
        resolution: string | null;
        original_outcome: string;
        corrected_outcome: string | null;
        is_self_report: boolean;
        votes_required: number;
        vote_count: number;
        expires_at: string;
        resolved_at: string | null;
        created_at: string;
        time_remaining: string | null;
    };
    item: {
        id: string;
        type: 'wager' | 'elimination';
        title: string;
    };
    reporter: {
        id: string;
        name: string;
    };
    accused: {
        id: string;
        name: string;
    };
    group: {
        id: string;
        name: string;
        currency: string;
    };
    votes: Vote[];
    voteTally: {
        original_correct: number;
        different_outcome: number;
        not_yet_determinable: number;
    };
    outcomeOptions: string[];
    canVote: boolean;
    userVote: {
        vote_outcome: string;
        selected_outcome: string | null;
    } | null;
    isReporter: boolean;
    isAccused: boolean;
}>();

const now = ref(Date.now());
const expiresAt = new Date(props.dispute.expires_at);

// Update countdown every second
const intervalId = setInterval(() => { now.value = Date.now(); }, 1000);

onUnmounted(() => {
    clearInterval(intervalId);
});

const timeRemaining = computed(() => {
    if (props.dispute.status === 'resolved') return null;

    const diff = expiresAt.getTime() - now.value;
    if (diff < 0) return 'Voting ended';

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const secs = Math.floor((diff % (1000 * 60)) / 1000);

    if (hours > 0) return `${hours}h ${mins}m ${secs}s remaining`;
    return `${mins}m ${secs}s remaining`;
});

const statusColor = computed(() => {
    if (props.dispute.status === 'resolved') {
        switch (props.dispute.resolution) {
            case 'original_correct': return 'text-green-600 dark:text-green-400';
            case 'fraud_confirmed': return 'text-red-600 dark:text-red-400';
            case 'premature_settlement': return 'text-amber-600 dark:text-amber-400';
            default: return 'text-neutral-600 dark:text-neutral-400';
        }
    }
    return 'text-blue-600 dark:text-blue-400';
});

const statusIcon = computed(() => {
    if (props.dispute.status === 'resolved') {
        switch (props.dispute.resolution) {
            case 'original_correct': return '✅';
            case 'fraud_confirmed': return '🚨';
            case 'premature_settlement': return '⏰';
            default: return '❓';
        }
    }
    return '⚖️';
});

const statusText = computed(() => {
    if (props.dispute.status === 'resolved') {
        switch (props.dispute.resolution) {
            case 'original_correct': return 'Original Upheld';
            case 'fraud_confirmed': return 'Fraud Confirmed';
            case 'premature_settlement': return 'Premature Settlement';
            default: return 'Resolved';
        }
    }
    return 'Pending Vote';
});

const resolutionDescription = computed(() => {
    if (props.dispute.status !== 'resolved') return null;

    switch (props.dispute.resolution) {
        case 'original_correct':
            return `The group confirmed the original outcome was correct. ${props.reporter.name} has been penalized for filing a false dispute.`;
        case 'fraud_confirmed':
            return `The group confirmed the settlement was fraudulent. ${props.accused.name} has been penalized and the outcome has been corrected.`;
        case 'premature_settlement':
            return `The group determined the settlement was premature. ${props.accused.name} has been penalized and the item has been reopened for settlement.`;
        default:
            return null;
    }
});

// Check if outcome options suggest a text/number input
const needsCustomInput = computed(() => {
    if (props.outcomeOptions.length === 1) {
        const opt = props.outcomeOptions[0].toLowerCase();
        return opt.includes('enter') || opt.includes('select') || opt.includes('number') || opt.includes('date');
    }
    return false;
});

const isEliminationDispute = computed(() => props.item.type === 'elimination');

// Vote form
const voteForm = useForm<{
    vote_outcome: string;
    selected_outcome: string;
}>({
    vote_outcome: '',
    selected_outcome: '',
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

const submitVote = () => {
    showToast.value = false;

    voteForm.post(`/disputes/${props.dispute.id}/vote`, {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Your vote has been recorded!';
            showToast.value = true;
        },
        onError: (errors) => {
            toastType.value = 'error';
            const firstError = Object.values(errors)[0];
            toastMessage.value = firstError
                ? (Array.isArray(firstError) ? firstError[0] : firstError)
                : 'Failed to submit vote. Please try again.';
            showToast.value = true;
        },
    });
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getVoteIcon = (outcome: string) => {
    switch (outcome) {
        case 'original_correct': return '✅';
        case 'different_outcome': return '🔄';
        case 'not_yet_determinable': return '⏳';
        default: return '❓';
    }
};

const itemUrl = computed(() => {
    return props.item.type === 'wager'
        ? `/wager/${props.item.id}`
        : `/elimination/${props.item.id}`;
});
</script>

<template>
    <AppLayout title="Dispute Details">
        <Head title="Dispute Details" />

        <div class="max-w-4xl mx-auto py-12 px-4">
            <!-- Header Card -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl">{{ statusIcon }}</span>
                            <span class="text-xl font-bold" :class="statusColor">{{ statusText }}</span>
                        </div>
                        <h1 class="text-lg text-neutral-700 dark:text-neutral-300">
                            Dispute on {{ item.type === 'wager' ? 'Wager' : 'Elimination Challenge' }}:
                        </h1>
                        <Link :href="itemUrl" class="text-xl font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            {{ item.title }}
                        </Link>
                    </div>
                    <div v-if="dispute.status === 'pending' && timeRemaining" class="text-right">
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Time Left</p>
                        <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ timeRemaining }}</p>
                    </div>
                </div>

                <!-- Self-report badge -->
                <div v-if="dispute.is_self_report" class="mb-4 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        <strong>Self-Report:</strong> {{ reporter.name }} is reporting their own mistake (reduced penalty applies)
                    </p>
                </div>

                <!-- Parties -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Reporter</p>
                        <p class="font-bold text-neutral-900 dark:text-white">
                            {{ reporter.name }}
                            <span v-if="isReporter" class="text-xs text-blue-600 dark:text-blue-400">(You)</span>
                        </p>
                    </div>
                    <div class="p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            {{ isEliminationDispute ? 'Accused Participant' : 'Settler' }}
                        </p>
                        <p class="font-bold text-neutral-900 dark:text-white">
                            {{ accused.name }}
                            <span v-if="isAccused" class="text-xs text-red-600 dark:text-red-400">(You)</span>
                        </p>
                    </div>
                </div>

                <!-- Original vs Corrected Outcome -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Original Outcome (Disputed)</p>
                        <p class="font-bold text-amber-700 dark:text-amber-300">{{ dispute.original_outcome }}</p>
                    </div>
                    <div v-if="dispute.corrected_outcome" class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Corrected Outcome</p>
                        <p class="font-bold text-green-700 dark:text-green-300">{{ dispute.corrected_outcome }}</p>
                    </div>
                </div>

                <!-- Resolution Description -->
                <div v-if="resolutionDescription" class="p-4 bg-neutral-100 dark:bg-neutral-700 rounded-lg mb-6">
                    <p class="text-neutral-700 dark:text-neutral-300">{{ resolutionDescription }}</p>
                </div>

                <!-- Vote Progress -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-neutral-600 dark:text-neutral-400">Votes: {{ dispute.vote_count }} / {{ dispute.votes_required }} required</span>
                    </div>
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-3">
                        <div
                            class="bg-blue-600 dark:bg-blue-500 h-3 rounded-full transition-all"
                            :style="{ width: `${Math.min(100, (dispute.vote_count / dispute.votes_required) * 100)}%` }"
                        ></div>
                    </div>
                </div>

                <!-- Vote Tally -->
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Original Correct</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ voteTally.original_correct }}</p>
                    </div>
                    <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Different Outcome</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ voteTally.different_outcome }}</p>
                    </div>
                    <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Too Early</p>
                        <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ voteTally.not_yet_determinable }}</p>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="mt-4 text-sm text-neutral-500 dark:text-neutral-400">
                    <span>Filed {{ formatDate(dispute.created_at) }}</span>
                    <span v-if="dispute.resolved_at" class="mx-2">•</span>
                    <span v-if="dispute.resolved_at">Resolved {{ formatDate(dispute.resolved_at) }}</span>
                </div>
            </div>

            <!-- Voting Form (if can vote) -->
            <div v-if="canVote" class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">Cast Your Vote</h2>

                <form @submit.prevent="submitVote" class="space-y-4">
                    <!-- Vote Options -->
                    <div class="space-y-3">
                        <!-- Original Correct -->
                        <label class="flex items-start gap-3 p-4 border-2 border-neutral-300 dark:border-neutral-600 rounded-lg cursor-pointer transition-all hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:has-[:checked]:bg-green-900/20">
                            <input
                                v-model="voteForm.vote_outcome"
                                type="radio"
                                value="original_correct"
                                required
                                class="mt-1 w-5 h-5"
                            />
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">✅ Original outcome is correct</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                    The settlement was accurate. Reporter will be penalized.
                                </p>
                            </div>
                        </label>

                        <!-- Different Outcome -->
                        <label class="flex items-start gap-3 p-4 border-2 border-neutral-300 dark:border-neutral-600 rounded-lg cursor-pointer transition-all hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20">
                            <input
                                v-model="voteForm.vote_outcome"
                                type="radio"
                                value="different_outcome"
                                required
                                class="mt-1 w-5 h-5"
                            />
                            <div class="flex-1">
                                <p class="font-medium text-neutral-900 dark:text-white">🔄 Different outcome is correct</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">
                                    The settlement was wrong. {{ accused.name }} will be penalized.
                                </p>

                                <!-- Outcome selection (shown when this option selected) -->
                                <div v-if="voteForm.vote_outcome === 'different_outcome'" class="mt-3">
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                                        What should the correct outcome be?
                                    </label>

                                    <!-- For elimination disputes or simple options -->
                                    <div v-if="!needsCustomInput" class="space-y-2">
                                        <label
                                            v-for="option in outcomeOptions"
                                            :key="option"
                                            class="flex items-center gap-2 p-2 border border-neutral-200 dark:border-neutral-600 rounded cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-700"
                                        >
                                            <input
                                                v-model="voteForm.selected_outcome"
                                                type="radio"
                                                :value="option"
                                                class="w-4 h-4"
                                            />
                                            <span class="text-neutral-900 dark:text-white">{{ option }}</span>
                                        </label>
                                    </div>

                                    <!-- For numeric/text inputs -->
                                    <input
                                        v-else
                                        v-model="voteForm.selected_outcome"
                                        type="text"
                                        :placeholder="outcomeOptions[0]"
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                                    />
                                </div>
                            </div>
                        </label>

                        <!-- Not Yet Determinable -->
                        <label class="flex items-start gap-3 p-4 border-2 border-neutral-300 dark:border-neutral-600 rounded-lg cursor-pointer transition-all hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 dark:has-[:checked]:bg-amber-900/20">
                            <input
                                v-model="voteForm.vote_outcome"
                                type="radio"
                                value="not_yet_determinable"
                                required
                                class="mt-1 w-5 h-5"
                            />
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">⏳ Too early to determine</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                    The settlement was premature. {{ accused.name }} will be penalized and the item reopened.
                                </p>
                            </div>
                        </label>
                    </div>

                    <FormError :error="voteForm.errors.vote_outcome" />
                    <FormError :error="voteForm.errors.selected_outcome" />

                    <button
                        type="submit"
                        :disabled="voteForm.processing || !voteForm.vote_outcome || (voteForm.vote_outcome === 'different_outcome' && !voteForm.selected_outcome)"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
                    >
                        <svg v-if="voteForm.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ voteForm.processing ? 'Submitting...' : 'Submit Vote' }}</span>
                    </button>
                </form>
            </div>

            <!-- User's existing vote -->
            <div v-else-if="userVote" class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">Your Vote</h2>
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="font-medium text-blue-700 dark:text-blue-300">
                        {{ getVoteIcon(userVote.vote_outcome) }}
                        {{ userVote.vote_outcome === 'original_correct' ? 'Original outcome is correct' :
                           userVote.vote_outcome === 'different_outcome' ? 'Different outcome' :
                           'Too early to determine' }}
                    </p>
                    <p v-if="userVote.selected_outcome" class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                        Selected: {{ userVote.selected_outcome }}
                    </p>
                </div>
            </div>

            <!-- Cannot vote message -->
            <div v-else-if="dispute.status === 'pending'" class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">Voting</h2>
                <div class="p-4 bg-neutral-100 dark:bg-neutral-700 rounded-lg">
                    <p class="text-neutral-600 dark:text-neutral-400">
                        <span v-if="isReporter">You cannot vote on disputes you filed.</span>
                        <span v-else-if="isAccused">You cannot vote on disputes against you.</span>
                        <span v-else>You are not eligible to vote on this dispute.</span>
                    </p>
                </div>
            </div>

            <!-- Vote History -->
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">
                    Votes ({{ votes.length }})
                </h2>

                <div v-if="votes.length > 0" class="space-y-3">
                    <div
                        v-for="vote in votes"
                        :key="vote.id"
                        class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-xl">{{ getVoteIcon(vote.vote_outcome) }}</span>
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">{{ vote.voter_name }}</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ vote.vote_outcome_label }}</p>
                                <p v-if="vote.selected_outcome" class="text-xs text-neutral-500 dark:text-neutral-500">
                                    → {{ vote.selected_outcome }}
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
                            {{ formatDate(vote.created_at) }}
                        </span>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                    No votes yet
                </div>
            </div>

            <!-- Back Navigation -->
            <div class="flex justify-center gap-4">
                <Link :href="itemUrl" class="text-blue-600 dark:text-blue-400 hover:underline">
                    ← Back to {{ item.type === 'wager' ? 'Wager' : 'Challenge' }}
                </Link>
                <span class="text-neutral-400">|</span>
                <a href="/me" class="text-blue-600 dark:text-blue-400 hover:underline">
                    Dashboard
                </a>
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
