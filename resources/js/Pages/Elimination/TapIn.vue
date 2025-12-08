<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    challenge: {
        id: string;
        description: string;
        elimination_trigger: string;
        elimination_mode: 'last_man_standing' | 'deadline';
        point_pot: number;
        buy_in_amount: number;
        tap_in_deadline: string | null;
        completion_deadline: string | null;
        participant_count: number;
        min_participants: number;
    };
    group: {
        id: string;
        name: string;
        currency: string;
    };
    user: {
        id: string;
        name: string;
        balance: number;
    };
    canTapIn: boolean;
    alreadyParticipating: boolean;
}>();

const form = useForm({});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Check if user has sufficient balance
const hasSufficientBalance = computed(() => {
    return props.user.balance >= props.challenge.buy_in_amount;
});

// Calculate potential winnings per survivor
const potentialWinnings = computed(() => {
    const participantsAfterJoin = props.challenge.participant_count + 1;
    return Math.floor(props.challenge.point_pot / participantsAfterJoin);
});

// Format date
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'No deadline';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Elimination mode display
const eliminationModeText = computed(() => {
    return props.challenge.elimination_mode === 'last_man_standing'
        ? 'Last Man Standing'
        : 'Deadline Mode';
});

const submit = () => {
    if (!hasSufficientBalance.value) {
        toastType.value = 'error';
        toastMessage.value = 'Insufficient balance to tap in.';
        showToast.value = true;
        return;
    }

    if (!props.canTapIn) {
        toastType.value = 'error';
        toastMessage.value = 'You cannot tap in to this challenge.';
        showToast.value = true;
        return;
    }

    showToast.value = false;

    form.post(`/elimination/${props.challenge.id}/tap-in`, {
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to tap in. Please try again.';
            showToast.value = true;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Tap In - Elimination Challenge" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-3xl">🎯</span>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">
                            Tap In
                        </h1>
                    </div>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ group.name }}
                    </p>
                </div>

                <!-- Already Participating Warning -->
                <div v-if="alreadyParticipating" class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <p class="text-amber-700 dark:text-amber-300 font-medium">
                        You're already participating in this challenge!
                    </p>
                </div>

                <!-- Challenge Details -->
                <div class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-1">
                            {{ challenge.description }}
                        </h2>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">
                            <span class="font-medium">Trigger:</span> {{ challenge.elimination_trigger }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-neutral-500 dark:text-neutral-400">Mode:</span>
                            <span class="ml-1 font-medium text-neutral-900 dark:text-white">
                                {{ eliminationModeText }}
                            </span>
                        </div>
                        <div>
                            <span class="text-neutral-500 dark:text-neutral-400">Participants:</span>
                            <span class="ml-1 font-medium text-neutral-900 dark:text-white">
                                {{ challenge.participant_count }} / {{ challenge.min_participants }} min
                            </span>
                        </div>
                        <div v-if="challenge.tap_in_deadline">
                            <span class="text-neutral-500 dark:text-neutral-400">Tap In By:</span>
                            <span class="ml-1 font-medium text-neutral-900 dark:text-white">
                                {{ formatDate(challenge.tap_in_deadline) }}
                            </span>
                        </div>
                        <div v-if="challenge.completion_deadline">
                            <span class="text-neutral-500 dark:text-neutral-400">Ends:</span>
                            <span class="ml-1 font-medium text-neutral-900 dark:text-white">
                                {{ formatDate(challenge.completion_deadline) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Prize Pot -->
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Current Prize Pot</span>
                        <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ challenge.point_pot }} {{ group.currency }}
                        </span>
                    </div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        If you survive, you could win ~{{ potentialWinnings }} {{ group.currency }}
                    </p>
                </div>

                <!-- Buy-in Amount -->
                <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Buy-in Required</span>
                        <span class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {{ challenge.buy_in_amount }} {{ group.currency }}
                        </span>
                    </div>
                </div>

                <!-- User Balance -->
                <div class="mb-6 p-3 rounded-lg" :class="hasSufficientBalance ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-600 dark:text-neutral-300">Your balance:</span>
                        <span class="font-semibold" :class="hasSufficientBalance ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
                            {{ user.balance }} {{ group.currency }}
                        </span>
                    </div>
                    <p v-if="!hasSufficientBalance" class="text-xs text-red-600 dark:text-red-400 mt-1">
                        Insufficient balance to tap in
                    </p>
                </div>

                <!-- Warning -->
                <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <div class="flex items-start gap-2">
                        <span class="text-lg">⚠️</span>
                        <div class="text-sm text-amber-700 dark:text-amber-300">
                            <p class="font-medium mb-1">Important:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Your buy-in is non-refundable unless the challenge is cancelled</li>
                                <li>If you're eliminated, you lose your buy-in</li>
                                <li>Survivors split the entire pot</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <form @submit.prevent="submit">
                    <button
                        type="submit"
                        :disabled="form.processing || !canTapIn || !hasSufficientBalance || alreadyParticipating"
                        class="w-full px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold text-lg inline-flex items-center justify-center gap-2 shadow-lg"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Tapping In...' : `Tap In (${challenge.buy_in_amount} ${group.currency})` }}</span>
                    </button>
                </form>
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
