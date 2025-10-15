<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';
import FormError from '@/Components/FormError.vue';
import ShortAnswerInput from '@/Components/ShortAnswerInput.vue';
import RankingInput from '@/Components/RankingInput.vue';

const props = defineProps<{
    wager: {
        id: string;
        title: string;
        description?: string;
        type: 'numeric' | 'date' | 'short_answer' | 'top_n_ranking';
        type_config: {
            max_length?: number;
            options?: string[];
            n?: number;
        };
        numeric_min?: number | null;
        numeric_max?: number | null;
        numeric_winner_type?: 'closest' | 'exact';
        date_min?: string | null;
        date_max?: string | null;
        date_winner_type?: 'closest' | 'exact';
        stake_amount: number;
        betting_closes_at: string;
        group: {
            id: string;
            name: string;
        };
        creator: {
            name: string;
        };
    };
    user: {
        id: string;
        name: string;
        balance: number;
    };
}>();

const form = useForm({
    answer_value: props.wager.type === 'top_n_ranking' ? [] as string[] : '',
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Check if user has sufficient balance
const hasSufficientBalance = computed(() => {
    return props.user.balance >= props.wager.stake_amount;
});

// Format date
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const submit = () => {
    if (!hasSufficientBalance.value) {
        toastType.value = 'error';
        toastMessage.value = 'Insufficient balance to join this wager.';
        showToast.value = true;
        return;
    }

    // Clear previous errors and toasts
    showToast.value = false;

    form.post(`/wager/${props.wager.id}/join`, {
        onSuccess: () => {
            // Will redirect to success page via backend
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to join wager. Please try again.';
            showToast.value = true;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Join Wager: ${wager.title}`" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">
                        📝 Join Wager
                    </h1>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ wager.group.name }}
                    </p>
                </div>

                <!-- Wager Details -->
                <div class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            {{ wager.title }}
                        </h2>
                        <p v-if="wager.description" class="text-sm text-neutral-600 dark:text-neutral-300 mt-1">
                            {{ wager.description }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm">
                        <div>
                            <span class="text-neutral-500 dark:text-neutral-400">Created by:</span>
                            <span class="ml-1 font-medium text-neutral-900 dark:text-white">
                                {{ wager.creator.name }}
                            </span>
                        </div>
                        <div>
                            <span class="text-neutral-500 dark:text-neutral-400">Stake:</span>
                            <span class="ml-1 font-medium text-neutral-900 dark:text-white">
                                {{ wager.stake_amount }} points
                            </span>
                        </div>
                        <div>
                            <span class="text-neutral-500 dark:text-neutral-400">Closes:</span>
                            <span class="ml-1 font-medium text-neutral-900 dark:text-white">
                                {{ formatDate(wager.betting_closes_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- User Balance -->
                <div class="mb-6 p-3 rounded-lg" :class="hasSufficientBalance ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-600 dark:text-neutral-300">Your balance:</span>
                        <span class="font-semibold" :class="hasSufficientBalance ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
                            {{ user.balance }} points
                        </span>
                    </div>
                    <p v-if="!hasSufficientBalance" class="text-xs text-red-600 dark:text-red-400 mt-1">
                        ⚠️ Insufficient balance to join this wager
                    </p>
                </div>

                <!-- Dynamic Input Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Numeric Input -->
                    <div v-if="wager.type === 'numeric'">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Your Numeric Guess *
                        </label>
                        <input
                            v-model.number="form.answer_value"
                            type="number"
                            required
                            :min="wager.numeric_min ?? undefined"
                            :max="wager.numeric_max ?? undefined"
                            placeholder="Enter a number"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            <span v-if="wager.numeric_min !== null && wager.numeric_max !== null">
                                Must be between {{ wager.numeric_min }} and {{ wager.numeric_max }}
                            </span>
                            <span v-else-if="wager.numeric_min !== null">
                                Must be at least {{ wager.numeric_min }}
                            </span>
                            <span v-else-if="wager.numeric_max !== null">
                                Must be at most {{ wager.numeric_max }}
                            </span>
                            • Winner: {{ wager.numeric_winner_type === 'exact' ? 'Exact match only' : 'Closest guess' }}
                        </p>
                        <FormError :error="form.errors.answer_value" />
                    </div>

                    <!-- Date Input -->
                    <div v-if="wager.type === 'date'">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Your Date Prediction *
                        </label>
                        <input
                            v-model="form.answer_value"
                            type="date"
                            required
                            :min="wager.date_min ?? undefined"
                            :max="wager.date_max ?? undefined"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            <span v-if="wager.date_min && wager.date_max">
                                Must be between {{ wager.date_min }} and {{ wager.date_max }}
                            </span>
                            <span v-else-if="wager.date_min">
                                Must be on or after {{ wager.date_min }}
                            </span>
                            <span v-else-if="wager.date_max">
                                Must be on or before {{ wager.date_max }}
                            </span>
                            • Winner: {{ wager.date_winner_type === 'exact' ? 'Exact date only' : 'Closest date' }}
                        </p>
                        <FormError :error="form.errors.answer_value" />
                    </div>

                    <!-- Short Answer Input -->
                    <div v-if="wager.type === 'short_answer'">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Your Answer *
                        </label>
                        <ShortAnswerInput
                            v-model="form.answer_value as string"
                            :max-length="wager.type_config.max_length || 100"
                            placeholder="Enter your answer..."
                        />
                        <FormError :error="form.errors.answer_value" />
                    </div>

                    <!-- Ranking Input -->
                    <div v-if="wager.type === 'top_n_ranking'">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Your Top {{ wager.type_config.n }} Ranking *
                        </label>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">
                            Drag to reorder or use the buttons to arrange your top {{ wager.type_config.n }} picks
                        </p>
                        <RankingInput
                            v-model="form.answer_value as string[]"
                            :options="wager.type_config.options || []"
                            :n="wager.type_config.n || 3"
                        />
                        <FormError :error="form.errors.answer_value" />
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing || !hasSufficientBalance"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium inline-flex items-center justify-center gap-2"
                        >
                            <!-- Loading spinner -->
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Joining...' : `Join Wager (${wager.stake_amount} points)` }}</span>
                        </button>
                    </div>
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
