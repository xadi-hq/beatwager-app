<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';
import FormError from '@/Components/FormError.vue';
import FormErrorBox from '@/Components/FormErrorBox.vue';

const props = defineProps<{
    wager: any;
    user: any;
    canSettle: boolean;
    isPastDeadline: boolean;
}>();

const now = ref(Date.now());
const deadline = new Date(props.wager.deadline);
const settledAt = props.wager.settled_at ? new Date(props.wager.settled_at) : null;

// Update countdown every second
const intervalId = setInterval(() => { now.value = Date.now(); }, 1000);

// Clean up interval when component unmounts
onUnmounted(() => {
    clearInterval(intervalId);
});

const timeRemaining = computed(() => {
    const diff = deadline.getTime() - now.value;
    if (diff < 0) return 'Deadline passed';
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const secs = Math.floor((diff % (1000 * 60)) / 1000);
    
    if (days > 0) return `${days}d ${hours}h ${mins}m remaining`;
    if (hours > 0) return `${hours}h ${mins}m ${secs}s remaining`;
    return `${mins}m ${secs}s remaining`;
});

const deadlinePassed = computed(() => {
    if (!props.isPastDeadline) return '';

    const diff = now.value - deadline.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

    if (days > 0) return `${days}d ${hours}h ago`;
    if (hours > 0) return `${hours}h ago`;
    return 'Recently';
});

// Sort entries by ranking for settled wagers
const sortedEntries = computed(() => {
    if (props.wager.status !== 'settled') return props.wager.entries;
    
    // Sort by: 1) Winner first, 2) Points won descending, 3) Points wagered descending
    return [...props.wager.entries].sort((a, b) => {
        if (a.is_winner !== b.is_winner) return b.is_winner ? 1 : -1;
        if (a.points_won !== b.points_won) return (b.points_won || 0) - (a.points_won || 0);
        return b.points_wagered - a.points_wagered;
    });
});

// Add medals for top 3
const getMedal = (index: number, entry: any) => {
    if (props.wager.status !== 'settled') return '';
    if (!entry.is_winner) return '';
    if (index === 0) return '🥇 ';
    if (index === 1) return '🥈 ';
    if (index === 2) return '🥉 ';
    return '';
};

const settlementForm = useForm({
    wager_id: props.wager.id,
    user_id: props.user.id,
    outcome_value: '',
    settlement_note: '',
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

const submitSettlement = () => {
    // Clear previous toasts
    showToast.value = false;

    settlementForm.post(`/wager/${props.wager.id}/settle`, {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Wager settled successfully!';
            showToast.value = true;

            // Backend will refresh the page to show settlement results
        },
        onError: (errors) => {
            toastType.value = 'error';

            // Show first validation error if available
            const firstError = Object.values(errors)[0];
            toastMessage.value = firstError
                ? (Array.isArray(firstError) ? firstError[0] : firstError)
                : 'Failed to settle wager. Please try again.';

            showToast.value = true;
        },
    });
};
</script>

<template>
    <AppLayout :title="wager.title">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <!-- Mobile/Tablet: Single column layout -->
            <div class="lg:hidden">
                <!-- Wager Header -->
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                    {{ wager.title }}
                </h1>
                <p v-if="wager.description" class="text-neutral-600 dark:text-neutral-300 mb-4">
                    {{ wager.description }}
                </p>

                <!-- Status and Metadata -->
                <div class="mb-4">
                    <!-- Active wager: countdown -->
                    <div v-if="!isPastDeadline" class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                        ⏰ {{ timeRemaining }}
                    </div>

                    <!-- Past deadline, awaiting settlement -->
                    <div v-else-if="wager.status === 'open'" class="text-2xl font-bold text-amber-600 dark:text-amber-400 mb-2">
                        ⏰ Awaiting Settlement
                    </div>

                    <!-- Settled wager: outcome -->
                    <div v-else>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">
                            ✅ Outcome: {{ wager.outcome_value }}
                        </div>
                        <div v-if="wager.settlement_note" class="mb-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded border-l-4 border-blue-500">
                            <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Settlement Note:</p>
                            <p class="text-neutral-600 dark:text-neutral-400 mt-1">{{ wager.settlement_note }}</p>
                        </div>
                    </div>

                    <!-- Metadata line: Creator • Settler • Deadline -->
                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                        <span>Created by <strong class="text-neutral-700 dark:text-neutral-300">{{ wager.creator.name }}</strong></span>
                        <span v-if="wager.settler" class="mx-2">•</span>
                        <span v-if="wager.settler">
                            Settled by <strong class="text-neutral-700 dark:text-neutral-300">{{ wager.settler.name }}</strong>
                            <span v-if="wager.settled_at"> on {{ new Date(wager.settled_at).toLocaleString() }}</span>
                        </span>
                        <span v-if="isPastDeadline" class="mx-2">•</span>
                        <span v-if="isPastDeadline">Deadline {{ deadlinePassed }}</span>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ wager.participants_count }}</div>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">Participants</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ wager.total_points_wagered }}</div>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">Total {{ wager.currency || 'points' }}</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ wager.stake_amount }}</div>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">Stake</div>
                    </div>
                </div>

                    <!-- Back to Dashboard Button -->
                    <div class="mt-6">
                        <a href="/me" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Entries Table -->
                <div v-if="wager.entries.length > 0" class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                    {{ isPastDeadline ? 'Results' : 'Participants' }}
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-neutral-200 dark:border-neutral-700">
                            <tr>
                                <th class="text-left py-2 text-neutral-700 dark:text-neutral-200">User</th>
                                <th v-if="isPastDeadline" class="text-left py-2 text-neutral-700 dark:text-neutral-200">Answer</th>
                                <th class="text-right py-2 text-neutral-700 dark:text-neutral-200">Balance</th>
                                <th class="text-right py-2 text-neutral-700 dark:text-neutral-200">Wagered</th>
                                <th v-if="wager.status === 'settled'" class="text-right py-2 text-neutral-700 dark:text-neutral-200">Won</th>
                                <th v-if="wager.status === 'settled'" class="text-right py-2 text-neutral-700 dark:text-neutral-200">New Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(entry, index) in sortedEntries" :key="entry.id" class="border-b border-neutral-100 dark:border-neutral-700">
                                <td class="py-2 text-neutral-900 dark:text-neutral-100">
                                    {{ getMedal(index, entry) }}{{ entry.user_name }}
                                </td>
                                <td v-if="isPastDeadline" class="py-2 font-medium" :class="entry.is_winner ? 'text-green-600 dark:text-green-400' : 'text-neutral-600 dark:text-neutral-400'">
                                    {{ entry.answer_value }}
                                </td>
                                <td class="text-right py-2 text-neutral-600 dark:text-neutral-400 text-sm">
                                    {{ entry.user_balance }}
                                </td>
                                <td class="text-right py-2 text-neutral-900 dark:text-neutral-100">
                                    {{ entry.points_wagered }}
                                </td>
                                <td v-if="wager.status === 'settled'" class="text-right py-2 font-bold" :class="entry.points_won ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    <span v-if="entry.points_won">+{{ entry.points_won }}</span>
                                    <span v-else>-{{ entry.points_wagered }}</span>
                                </td>
                                <td v-if="wager.status === 'settled'" class="text-right py-2 text-neutral-900 dark:text-neutral-100 font-semibold">
                                    {{ entry.user_balance + (entry.points_won || -entry.points_wagered) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Settlement Form (if can settle) -->
                <div v-if="canSettle" class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                    ⚙️ Settle Wager
                </h2>
                <form @submit.prevent="submitSettlement" class="space-y-4">
                    <!-- Outcome Selection -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                            What was the outcome? *
                        </label>

                        <!-- Binary -->
                        <div v-if="wager.type === 'binary'" class="space-y-2">
                            <label class="flex items-center">
                                <input v-model="settlementForm.outcome_value" type="radio" value="yes" required class="mr-2" />
                                <span class="text-neutral-900 dark:text-neutral-100">Yes</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="settlementForm.outcome_value" type="radio" value="no" required class="mr-2" />
                                <span class="text-neutral-900 dark:text-neutral-100">No</span>
                            </label>
                        </div>

                        <!-- Multiple Choice -->
                        <div v-else-if="wager.type === 'multiple_choice'" class="space-y-2">
                            <label v-for="option in wager.options" :key="option" class="flex items-center">
                                <input v-model="settlementForm.outcome_value" type="radio" :value="option.toLowerCase()" required class="mr-2" />
                                <span class="text-neutral-900 dark:text-neutral-100">{{ option }}</span>
                            </label>
                        </div>

                        <FormError :error="settlementForm.errors.outcome_value" />
                    </div>

                    <!-- Settlement Note -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                            Settlement Note (Optional)
                        </label>
                        <textarea
                            v-model="settlementForm.settlement_note"
                            rows="3"
                            placeholder="Any additional notes..."
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                        ></textarea>
                        <FormError :error="settlementForm.errors.settlement_note" />
                    </div>

                    <!-- General Error (for user_id or other field errors) -->
                    <FormErrorBox :errors="settlementForm.errors" :exclude="['outcome_value', 'settlement_note']" />

                    <button
                        type="submit"
                        :disabled="settlementForm.processing"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
                    >
                        <!-- Loading spinner -->
                        <svg v-if="settlementForm.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ settlementForm.processing ? 'Settling...' : 'Settle Wager' }}</span>
                    </button>
                </form>
                </div>
            </div>

            <!-- Desktop: Two column layout -->
            <div class="hidden lg:grid lg:grid-cols-2 lg:gap-6">
                <!-- Left column: Wager details -->
                <div>
                    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                            {{ wager.title }}
                        </h1>
                        <p v-if="wager.description" class="text-neutral-600 dark:text-neutral-300 mb-4">
                            {{ wager.description }}
                        </p>

                        <!-- Status and Metadata -->
                        <div class="mb-4">
                            <!-- Active wager: countdown -->
                            <div v-if="!isPastDeadline" class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                                ⏰ {{ timeRemaining }}
                            </div>

                            <!-- Past deadline, awaiting settlement -->
                            <div v-else-if="wager.status === 'open'" class="text-2xl font-bold text-amber-600 dark:text-amber-400 mb-2">
                                ⏰ Awaiting Settlement
                            </div>

                            <!-- Settled wager: outcome -->
                            <div v-else>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-2">
                                    ✅ Outcome: {{ wager.outcome_value }}
                                </div>
                                <div v-if="wager.settlement_note" class="mb-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded border-l-4 border-blue-500">
                                    <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Settlement Note:</p>
                                    <p class="text-neutral-600 dark:text-neutral-400 mt-1">{{ wager.settlement_note }}</p>
                                </div>
                            </div>

                            <!-- Metadata line: Creator • Settler • Deadline -->
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                <span>Created by <strong class="text-neutral-700 dark:text-neutral-300">{{ wager.creator.name }}</strong></span>
                                <span v-if="wager.settler" class="mx-2">•</span>
                                <span v-if="wager.settler">
                                    Settled by <strong class="text-neutral-700 dark:text-neutral-300">{{ wager.settler.name }}</strong>
                                    <span v-if="wager.settled_at"> on {{ new Date(wager.settled_at).toLocaleString() }}</span>
                                </span>
                                <span v-if="isPastDeadline" class="mx-2">•</span>
                                <span v-if="isPastDeadline">Deadline {{ deadlinePassed }}</span>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-4 text-center mb-6">
                            <div>
                                <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ wager.participants_count }}</div>
                                <div class="text-sm text-neutral-500 dark:text-neutral-400">Participants</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ wager.total_points_wagered }}</div>
                                <div class="text-sm text-neutral-500 dark:text-neutral-400">Total {{ wager.currency || 'points' }}</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ wager.stake_amount }}</div>
                                <div class="text-sm text-neutral-500 dark:text-neutral-400">Stake</div>
                            </div>
                        </div>

                        <!-- Back to Dashboard Button -->
                        <div>
                            <a href="/me" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                                ← Back to Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- Settlement Form (if can settle) -->
                    <div v-if="canSettle" class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                        <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                            ⚙️ Settle Wager
                        </h2>
                        <form @submit.prevent="submitSettlement" class="space-y-4">
                            <!-- Outcome Selection -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                                    What was the outcome? *
                                </label>

                                <!-- Binary -->
                                <div v-if="wager.type === 'binary'" class="space-y-2">
                                    <label class="flex items-center">
                                        <input v-model="settlementForm.outcome_value" type="radio" value="yes" required class="mr-2" />
                                        <span class="text-neutral-900 dark:text-neutral-100">Yes</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input v-model="settlementForm.outcome_value" type="radio" value="no" required class="mr-2" />
                                        <span class="text-neutral-900 dark:text-neutral-100">No</span>
                                    </label>
                                </div>

                                <!-- Multiple Choice -->
                                <div v-else-if="wager.type === 'multiple_choice'" class="space-y-2">
                                    <label v-for="option in wager.options" :key="option" class="flex items-center">
                                        <input v-model="settlementForm.outcome_value" type="radio" :value="option.toLowerCase()" required class="mr-2" />
                                        <span class="text-neutral-900 dark:text-neutral-100">{{ option }}</span>
                                    </label>
                                </div>

                                <FormError :error="settlementForm.errors.outcome_value" />
                            </div>

                            <!-- Settlement Note -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                                    Settlement Note (Optional)
                                </label>
                                <textarea
                                    v-model="settlementForm.settlement_note"
                                    rows="3"
                                    placeholder="Any additional notes..."
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                                ></textarea>
                                <FormError :error="settlementForm.errors.settlement_note" />
                            </div>

                            <!-- General Error (for user_id or other field errors) -->
                            <FormErrorBox :errors="settlementForm.errors" :exclude="['outcome_value', 'settlement_note']" />

                            <button
                                type="submit"
                                :disabled="settlementForm.processing"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
                            >
                                <!-- Loading spinner -->
                                <svg v-if="settlementForm.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ settlementForm.processing ? 'Settling...' : 'Settle Wager' }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right column: Participants -->
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 mb-4">
                        {{ isPastDeadline ? 'Results' : 'Participants' }} ({{ wager.entries.length }})
                    </h2>
                    <div class="overflow-x-auto max-h-[800px] overflow-y-auto">
                        <table class="w-full">
                            <thead class="border-b border-neutral-200 dark:border-neutral-700 sticky top-0 bg-white dark:bg-neutral-800">
                                <tr>
                                    <th class="text-left py-2 text-neutral-700 dark:text-neutral-200">User</th>
                                    <th v-if="isPastDeadline" class="text-left py-2 text-neutral-700 dark:text-neutral-200">Answer</th>
                                    <th class="text-right py-2 text-neutral-700 dark:text-neutral-200">Balance</th>
                                    <th class="text-right py-2 text-neutral-700 dark:text-neutral-200">Wagered</th>
                                    <th v-if="wager.status === 'settled'" class="text-right py-2 text-neutral-700 dark:text-neutral-200">Won</th>
                                    <th v-if="wager.status === 'settled'" class="text-right py-2 text-neutral-700 dark:text-neutral-200">New Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(entry, index) in sortedEntries" :key="entry.id" class="border-b border-neutral-100 dark:border-neutral-700">
                                    <td class="py-2 text-neutral-900 dark:text-neutral-100">
                                        {{ getMedal(index, entry) }}{{ entry.user_name }}
                                    </td>
                                    <td v-if="isPastDeadline" class="py-2 font-medium" :class="entry.is_winner ? 'text-green-600 dark:text-green-400' : 'text-neutral-600 dark:text-neutral-400'">
                                        {{ entry.answer_value }}
                                    </td>
                                    <td class="text-right py-2 text-neutral-600 dark:text-neutral-400 text-sm">
                                        {{ entry.user_balance }}
                                    </td>
                                    <td class="text-right py-2 text-neutral-900 dark:text-neutral-100">
                                        {{ entry.points_wagered }}
                                    </td>
                                    <td v-if="wager.status === 'settled'" class="text-right py-2 font-bold" :class="entry.points_won ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                        <span v-if="entry.points_won">+{{ entry.points_won }}</span>
                                        <span v-else>-{{ entry.points_wagered }}</span>
                                    </td>
                                    <td v-if="wager.status === 'settled'" class="text-right py-2 text-neutral-900 dark:text-neutral-100 font-semibold">
                                        {{ entry.user_balance + (entry.points_won || -entry.points_wagered) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
