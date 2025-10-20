<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';
import FormError from '@/Components/FormError.vue';

const props = defineProps<{
    challenge: {
        id: string;
        description: string;
        amount: number;
        status: 'open' | 'accepted' | 'completed' | 'failed' | 'cancelled';
        completion_deadline: string;
        acceptance_deadline?: string;
        accepted_at?: string;
        submitted_at?: string;
        completed_at?: string;
        failed_at?: string;
        cancelled_at?: string;
        submission_note?: string;
        failure_reason?: string;
        currency: string;
    };
    creator: {
        id: string;
        name: string;
    };
    acceptor?: {
        id: string;
        name: string;
    };
    group: {
        id: string;
        name: string;
    };
    userRole: 'creator' | 'acceptor' | 'viewer';
}>();

// Computed property for absolute amount display
const absoluteAmount = computed(() => Math.abs(props.challenge.amount));

// Determine challenge type
const isOfferingService = computed(() => props.challenge.amount < 0);

const now = ref(Date.now());
const completionDeadline = new Date(props.challenge.completion_deadline);
const acceptanceDeadline = props.challenge.acceptance_deadline ? new Date(props.challenge.acceptance_deadline) : null;

// Update countdown every second
const intervalId = setInterval(() => { now.value = Date.now(); }, 1000);

// Clean up interval when component unmounts
onUnmounted(() => {
    clearInterval(intervalId);
});

const completionTimeRemaining = computed(() => {
    const diff = completionDeadline.getTime() - now.value;
    if (diff < 0) return 'Completion deadline passed';

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const secs = Math.floor((diff % (1000 * 60)) / 1000);

    if (days > 0) return `${days}d ${hours}h ${mins}m to complete`;
    if (hours > 0) return `${hours}h ${mins}m ${secs}s to complete`;
    return `${mins}m ${secs}s to complete`;
});

const acceptanceTimeRemaining = computed(() => {
    if (!acceptanceDeadline || props.challenge.status !== 'open') return null;
    
    const diff = acceptanceDeadline.getTime() - now.value;
    if (diff < 0) return 'Acceptance deadline passed';

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    if (days > 0) return `${days}d ${hours}h ${mins}m to accept`;
    if (hours > 0) return `${hours}h ${mins}m to accept`;
    return `${mins}m to accept`;
});

const statusColor = computed(() => {
    switch (props.challenge.status) {
        case 'open': return 'text-blue-600 dark:text-blue-400';
        case 'accepted': return 'text-amber-600 dark:text-amber-400';
        case 'completed': return 'text-green-600 dark:text-green-400';
        case 'failed': return 'text-red-600 dark:text-red-400';
        case 'cancelled': return 'text-neutral-600 dark:text-neutral-400';
        default: return 'text-neutral-600 dark:text-neutral-400';
    }
});

const statusIcon = computed(() => {
    switch (props.challenge.status) {
        case 'open': return '🔓';
        case 'accepted': return '⚡';
        case 'completed': return '✅';
        case 'failed': return '❌';
        case 'cancelled': return '🚫';
        default: return '❓';
    }
});

const statusText = computed(() => {
    switch (props.challenge.status) {
        case 'open': return 'Open for acceptance';
        case 'accepted': return 'In progress';
        case 'completed': return 'Completed';
        case 'failed': return 'Failed';
        case 'cancelled': return 'Cancelled';
        default: return 'Unknown';
    }
});

// Forms for different actions
const acceptForm = useForm({});
const submitForm = useForm({
    submission_note: '',
});
const approveForm = useForm({});
const rejectForm = useForm({
    reason: '',
});
const cancelForm = useForm({});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Modal states
const showSubmitModal = ref(false);
const showRejectModal = ref(false);
const showCancelModal = ref(false);

const acceptChallenge = () => {
    showToast.value = false;
    
    acceptForm.post(`/challenges/${props.challenge.id}/accept`, {
        onSuccess: (response: any) => {
            toastType.value = 'success';
            toastMessage.value = response.props?.flash?.success || 'Challenge accepted!';
            showToast.value = true;
            // Page will be refreshed by Inertia
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to accept challenge';
            showToast.value = true;
        },
    });
};

const submitChallenge = () => {
    showToast.value = false;
    
    submitForm.post(`/challenges/${props.challenge.id}/submit`, {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Challenge submitted for review!';
            showToast.value = true;
            showSubmitModal.value = false;
            submitForm.reset();
            // Page will be refreshed by Inertia
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to submit challenge';
            showToast.value = true;
        },
    });
};

const approveChallenge = () => {
    showToast.value = false;
    
    approveForm.post(`/challenges/${props.challenge.id}/approve`, {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Challenge approved! Points transferred.';
            showToast.value = true;
            // Page will be refreshed by Inertia
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to approve challenge';
            showToast.value = true;
        },
    });
};

const rejectChallenge = () => {
    showToast.value = false;
    
    rejectForm.post(`/challenges/${props.challenge.id}/reject`, {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Challenge rejected. Points returned.';
            showToast.value = true;
            showRejectModal.value = false;
            rejectForm.reset();
            // Page will be refreshed by Inertia
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to reject challenge';
            showToast.value = true;
        },
    });
};

const cancelChallenge = () => {
    showToast.value = false;
    
    cancelForm.post(`/challenges/${props.challenge.id}/cancel`, {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Challenge cancelled successfully.';
            showToast.value = true;
            showCancelModal.value = false;
            // Page will be refreshed by Inertia
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to cancel challenge';
            showToast.value = true;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Challenge Details" />
        <div class="max-w-7xl mx-auto px-4 py-12">
            <!-- Mobile/Tablet: Single column layout -->
            <div class="lg:hidden">
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                        {{ challenge.description }}
                    </h1>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                        <span>Created by <strong class="text-neutral-700 dark:text-neutral-300">{{ creator.name }}</strong></span>
                        <span class="mx-2">•</span>
                        <span>In <strong class="text-neutral-700 dark:text-neutral-300">{{ group.name }}</strong></span>
                    </div>

                    <!-- Reward -->
                    <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="text-sm text-neutral-600 dark:text-neutral-400">
                            {{ isOfferingService ? 'Will Earn' : 'Reward' }}
                        </div>
                        <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                            {{ absoluteAmount }} {{ challenge.currency }}
                        </div>
                        <div v-if="isOfferingService" class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            🫴 Service offering
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <div class="flex items-center gap-2 text-2xl font-bold mb-2" :class="statusColor">
                            <span>{{ statusIcon }}</span>
                            <span>{{ statusText }}</span>
                        </div>

                        <!-- Acceptor info (if accepted) -->
                        <div v-if="acceptor" class="text-sm text-neutral-500 dark:text-neutral-400">
                            <span>Accepted by <strong class="text-neutral-700 dark:text-neutral-300">{{ acceptor.name }}</strong></span>
                            <span v-if="challenge.accepted_at"> on {{ new Date(challenge.accepted_at).toLocaleString() }}</span>
                        </div>
                    </div>

                    <!-- Deadlines -->
                    <div class="space-y-4 mb-6">
                        <!-- Acceptance deadline (if open and has deadline) -->
                        <div v-if="challenge.status === 'open' && acceptanceDeadline" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">
                                Accept by
                            </div>
                            <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                                {{ acceptanceTimeRemaining }}
                            </div>
                            <div class="text-xs text-blue-700 dark:text-blue-300">
                                {{ acceptanceDeadline.toLocaleString() }}
                            </div>
                        </div>

                        <!-- Completion deadline -->
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <div class="text-sm font-medium text-amber-900 dark:text-amber-100 mb-1">
                                Complete by
                            </div>
                            <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mb-1">
                                <span v-if="challenge.status === 'accepted' || challenge.status === 'open'">
                                    {{ completionTimeRemaining }}
                                </span>
                                <span v-else>
                                    {{ completionDeadline.toLocaleString() }}
                                </span>
                            </div>
                            <div class="text-xs text-amber-700 dark:text-amber-300">
                                {{ completionDeadline.toLocaleString() }}
                            </div>
                        </div>
                    </div>

                    <!-- Back to Dashboard Button -->
                    <div>
                        <a href="/me" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Submission details (mobile) -->
            <div v-if="challenge.submitted_at" class="lg:hidden bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                    📋 Submission
                </h2>
                <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">
                    Submitted on {{ new Date(challenge.submitted_at).toLocaleString() }}
                </div>
                <div v-if="challenge.submission_note" class="p-3 bg-neutral-50 dark:bg-neutral-700 rounded border-l-4 border-blue-500">
                    <p class="text-neutral-700 dark:text-neutral-300">{{ challenge.submission_note }}</p>
                </div>
                <div v-else class="text-neutral-500 dark:text-neutral-400 italic">
                    No notes provided
                </div>
            </div>

            <!-- Completion/Failure details (mobile) -->
            <div v-if="challenge.completed_at || challenge.failed_at" class="lg:hidden bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                    <span v-if="challenge.completed_at">✅ Completed</span>
                    <span v-else>❌ Failed</span>
                </h2>
                <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">
                    <span v-if="challenge.completed_at">
                        Completed on {{ new Date(challenge.completed_at).toLocaleString() }}
                    </span>
                    <span v-else>
                        Failed on {{ new Date(challenge.failed_at!).toLocaleString() }}
                    </span>
                </div>
                <div v-if="challenge.failure_reason" class="p-3 bg-red-50 dark:bg-red-900/20 rounded border-l-4 border-red-500">
                    <p class="text-red-700 dark:text-red-300">{{ challenge.failure_reason }}</p>
                </div>
                <div v-if="challenge.completed_at" class="p-3 bg-green-50 dark:bg-green-900/20 rounded border-l-4 border-green-500">
                    <p class="text-green-700 dark:text-green-300">
                        Challenge completed successfully! Points transferred from {{ creator.name }} to {{ acceptor?.name }}.
                    </p>
                </div>
            </div>

            <!-- Actions (mobile) -->
            <div class="lg:hidden bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                    Actions
                </h2>

                <!-- Open challenge actions -->
                <div v-if="challenge.status === 'open'" class="space-y-4">
                    <!-- Accept challenge (non-creator) -->
                    <div v-if="userRole !== 'creator'">
                        <button
                            @click="acceptChallenge"
                            :disabled="acceptForm.processing"
                            class="w-full px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                        >
                            {{ acceptForm.processing ? 'Accepting...' : '🏃 Accept Challenge' }}
                        </button>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2">
                            First come, first served! Your acceptance will reserve the creator's points.
                        </p>
                    </div>

                    <!-- Cancel challenge (creator only) -->
                    <div v-if="userRole === 'creator'">
                        <button
                            @click="showCancelModal = true"
                            class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium"
                        >
                            Cancel Challenge
                        </button>
                    </div>

                    <!-- Viewer message -->
                    <div v-if="userRole === 'viewer'" class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                        This challenge is open for acceptance by group members.
                    </div>
                </div>

                <!-- Accepted challenge actions -->
                <div v-else-if="challenge.status === 'accepted'" class="space-y-4">
                    <!-- Submit completion (acceptor only) -->
                    <div v-if="userRole === 'acceptor'">
                        <button
                            @click="showSubmitModal = true"
                            class="w-full px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium"
                        >
                            ✅ Mark as Complete
                        </button>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2">
                            Submit your completion for the creator to review.
                        </p>
                    </div>

                    <!-- Waiting message -->
                    <div v-else class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                        <span v-if="userRole === 'creator'">
                            Waiting for {{ acceptor?.name }} to complete the challenge.
                        </span>
                        <span v-else>
                            {{ acceptor?.name }} is working on this challenge.
                        </span>
                    </div>

                    <!-- Review submission (creator only, if submitted) -->
                    <div v-if="userRole === 'creator' && challenge.submitted_at" class="flex gap-4">
                        <button
                            @click="approveChallenge"
                            :disabled="approveForm.processing"
                            class="flex-1 px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                        >
                            {{ approveForm.processing ? 'Approving...' : '✅ Approve' }}
                        </button>
                        <button
                            @click="showRejectModal = true"
                            class="flex-1 px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium"
                        >
                            ❌ Reject
                        </button>
                    </div>
                </div>

                <!-- Completed/failed/cancelled status -->
                <div v-else class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                    This challenge is {{ challenge.status }}.
                </div>
            </div>

            <!-- Desktop: Two column layout -->
            <div class="hidden lg:grid lg:grid-cols-2 lg:gap-6">
            <!-- Left column: Challenge details -->
            <div>
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                        {{ challenge.description }}
                    </h1>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                        <span>Created by <strong class="text-neutral-700 dark:text-neutral-300">{{ creator.name }}</strong></span>
                        <span class="mx-2">•</span>
                        <span>In <strong class="text-neutral-700 dark:text-neutral-300">{{ group.name }}</strong></span>
                    </div>

                    <!-- Reward -->
                    <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="text-sm text-neutral-600 dark:text-neutral-400">
                            {{ isOfferingService ? 'Will Earn' : 'Reward' }}
                        </div>
                        <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                            {{ absoluteAmount }} {{ challenge.currency }}
                        </div>
                        <div v-if="isOfferingService" class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            🫴 Service offering
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <div class="flex items-center gap-2 text-2xl font-bold mb-2" :class="statusColor">
                            <span>{{ statusIcon }}</span>
                            <span>{{ statusText }}</span>
                        </div>

                        <!-- Acceptor info (if accepted) -->
                        <div v-if="acceptor" class="text-sm text-neutral-500 dark:text-neutral-400">
                            <span>Accepted by <strong class="text-neutral-700 dark:text-neutral-300">{{ acceptor.name }}</strong></span>
                            <span v-if="challenge.accepted_at"> on {{ new Date(challenge.accepted_at).toLocaleString() }}</span>
                        </div>
                    </div>

                    <!-- Deadlines -->
                    <div class="space-y-4 mb-6">
                        <!-- Acceptance deadline (if open and has deadline) -->
                        <div v-if="challenge.status === 'open' && acceptanceDeadline" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">
                                Accept by
                            </div>
                            <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                                {{ acceptanceTimeRemaining }}
                            </div>
                            <div class="text-xs text-blue-700 dark:text-blue-300">
                                {{ acceptanceDeadline.toLocaleString() }}
                            </div>
                        </div>

                        <!-- Completion deadline -->
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <div class="text-sm font-medium text-amber-900 dark:text-amber-100 mb-1">
                                Complete by
                            </div>
                            <div class="text-xl font-bold text-amber-600 dark:text-amber-400 mb-1">
                                <span v-if="challenge.status === 'accepted' || challenge.status === 'open'">
                                    {{ completionTimeRemaining }}
                                </span>
                                <span v-else>
                                    {{ completionDeadline.toLocaleString() }}
                                </span>
                            </div>
                            <div class="text-xs text-amber-700 dark:text-amber-300">
                                {{ completionDeadline.toLocaleString() }}
                            </div>
                        </div>
                    </div>

                    <!-- Back to Dashboard Button -->
                    <div>
                        <a href="/me" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Submission details -->
                <div v-if="challenge.submitted_at" class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                        📋 Submission
                    </h2>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">
                        Submitted on {{ new Date(challenge.submitted_at).toLocaleString() }}
                    </div>
                    <div v-if="challenge.submission_note" class="p-3 bg-neutral-50 dark:bg-neutral-700 rounded border-l-4 border-blue-500">
                        <p class="text-neutral-700 dark:text-neutral-300">{{ challenge.submission_note }}</p>
                    </div>
                    <div v-else class="text-neutral-500 dark:text-neutral-400 italic">
                        No notes provided
                    </div>
                </div>

                <!-- Completion/Failure details -->
                <div v-if="challenge.completed_at || challenge.failed_at" class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                        <span v-if="challenge.completed_at">✅ Completed</span>
                        <span v-else>❌ Failed</span>
                    </h2>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">
                        <span v-if="challenge.completed_at">
                            Completed on {{ new Date(challenge.completed_at).toLocaleString() }}
                        </span>
                        <span v-else>
                            Failed on {{ new Date(challenge.failed_at!).toLocaleString() }}
                        </span>
                    </div>
                    <div v-if="challenge.failure_reason" class="p-3 bg-red-50 dark:bg-red-900/20 rounded border-l-4 border-red-500">
                        <p class="text-red-700 dark:text-red-300">{{ challenge.failure_reason }}</p>
                    </div>
                    <div v-if="challenge.completed_at" class="p-3 bg-green-50 dark:bg-green-900/20 rounded border-l-4 border-green-500">
                        <p class="text-green-700 dark:text-green-300">
                            Challenge completed successfully! Points transferred from {{ creator.name }} to {{ acceptor?.name }}.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right column: Actions -->
            <div>
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                        Actions
                    </h2>

                    <!-- Open challenge actions -->
                    <div v-if="challenge.status === 'open'" class="space-y-4">
                        <!-- Accept challenge (non-creator) -->
                        <div v-if="userRole !== 'creator'">
                            <button
                                @click="acceptChallenge"
                                :disabled="acceptForm.processing"
                                class="w-full px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                {{ acceptForm.processing ? 'Accepting...' : '🏃 Accept Challenge' }}
                            </button>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2">
                                First come, first served! Your acceptance will reserve the creator's points.
                            </p>
                        </div>

                        <!-- Cancel challenge (creator only) -->
                        <div v-if="userRole === 'creator'">
                            <button
                                @click="showCancelModal = true"
                                class="w-full px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium"
                            >
                                🚫 Cancel Challenge
                            </button>
                        </div>

                        <!-- Viewer message -->
                        <div v-if="userRole === 'viewer'" class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                            This challenge is open for acceptance by group members.
                        </div>
                    </div>

                    <!-- Accepted challenge actions -->
                    <div v-else-if="challenge.status === 'accepted'" class="space-y-4">
                        <!-- Submit completion (acceptor only) -->
                        <div v-if="userRole === 'acceptor'">
                            <button
                                @click="showSubmitModal = true"
                                class="w-full px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium"
                            >
                                ✅ Mark as Complete
                            </button>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-2">
                                Submit your completion for the creator to review.
                            </p>
                        </div>

                        <!-- Waiting message -->
                        <div v-else class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                            <span v-if="userRole === 'creator'">
                                Waiting for {{ acceptor?.name }} to complete the challenge.
                            </span>
                            <span v-else>
                                {{ acceptor?.name }} is working on this challenge.
                            </span>
                        </div>

                        <!-- Review submission (creator only, if submitted) -->
                        <div v-if="userRole === 'creator' && challenge.submitted_at" class="space-y-3">
                            <button
                                @click="approveChallenge"
                                :disabled="approveForm.processing"
                                class="w-full px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                {{ approveForm.processing ? 'Approving...' : '✅ Approve' }}
                            </button>
                            <button
                                @click="showRejectModal = true"
                                class="w-full px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium"
                            >
                                ❌ Reject
                            </button>
                        </div>
                    </div>

                    <!-- Completed/failed/cancelled status -->
                    <div v-else class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                        This challenge is {{ challenge.status }}.
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Submit Modal -->
        <div v-if="showSubmitModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">
                    Submit Completion
                </h3>
                <form @submit.prevent="submitChallenge">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Completion Note (optional)
                        </label>
                        <textarea
                            v-model="submitForm.submission_note"
                            rows="3"
                            placeholder="Describe what you did or provide proof..."
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        ></textarea>
                        <FormError :error="submitForm.errors.submission_note" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showSubmitModal = false"
                            class="px-4 py-2 bg-neutral-300 dark:bg-neutral-600 text-neutral-700 dark:text-neutral-300 rounded-md hover:bg-neutral-400 dark:hover:bg-neutral-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="submitForm.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ submitForm.processing ? 'Submitting...' : 'Submit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">
                    Reject Submission
                </h3>
                <form @submit.prevent="rejectChallenge">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Reason for rejection *
                        </label>
                        <textarea
                            v-model="rejectForm.reason"
                            rows="3"
                            required
                            placeholder="Explain why this doesn't meet the requirements..."
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        ></textarea>
                        <FormError :error="rejectForm.errors.reason" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showRejectModal = false"
                            class="px-4 py-2 bg-neutral-300 dark:bg-neutral-600 text-neutral-700 dark:text-neutral-300 rounded-md hover:bg-neutral-400 dark:hover:bg-neutral-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="rejectForm.processing"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ rejectForm.processing ? 'Rejecting...' : 'Reject' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">
                    Cancel Challenge
                </h3>
                <p class="text-neutral-600 dark:text-neutral-400 mb-6">
                    Are you sure you want to cancel this challenge? This action cannot be undone.
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