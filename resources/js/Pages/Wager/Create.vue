<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';
import FormError from '@/Components/FormError.vue';

const props = defineProps<{
    user: {
        id: string;
        name: string;
        telegram_username?: string;
    };
    defaultGroup: {
        id: string;
        name: string;
    } | null;
    groups: Array<{
        id: string;
        name: string;
        telegram_chat_title?: string;
    }>;
    groupMembers: Array<{
        name: string;
        points: number;
    }>;
}>();

// Default betting closes at to now + 6 hours
const getDefaultBettingClosesAt = () => {
    const now = new Date();
    now.setHours(now.getHours() + 6);
    // Format as datetime-local input format: YYYY-MM-DDTHH:mm
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const form = useForm({
    title: '',
    description: '',
    resolution_criteria: '',
    type: 'binary_yes_no' as 'binary_yes_no' | 'binary_over_under' | 'binary_before_after' | 'binary_custom' | 'multiple_choice' | 'numeric' | 'date' | 'short_answer' | 'top_n_ranking',
    group_id: props.defaultGroup?.id || '',
    stake_amount: 100,
    betting_closes_at: getDefaultBettingClosesAt(),
    expected_settlement_at: '',
    // Binary labels
    label_option_a: 'Yes',
    label_option_b: 'No',
    threshold_value: null as number | null,
    threshold_date: null as string | null,
    // Multiple choice & ranking
    options: ['', ''],
    max_length: 100,
    n: 3,
    // Numeric
    numeric_min: null as number | null,
    numeric_max: null as number | null,
    numeric_winner_type: 'closest' as 'closest' | 'exact',
    // Date
    date_min: null as string | null,
    date_max: null as string | null,
    date_winner_type: 'closest' as 'closest' | 'exact',
});

const wagerTypes = [
    { value: 'binary_yes_no', label: 'Yes/No Question' },
    { value: 'binary_over_under', label: 'Over/Under (with numeric threshold)' },
    { value: 'binary_before_after', label: 'Before/After (with date threshold)' },
    { value: 'binary_custom', label: 'Custom Binary' },
    { value: 'multiple_choice', label: 'Multiple Choice (e.g., 1/x/2 for soccer)' },
    { value: 'numeric', label: 'Numeric Guess (e.g., "How many goals?")' },
    { value: 'date', label: 'Date Prediction (e.g., "When will X happen?")' },
    { value: 'short_answer', label: 'Short Answer (e.g., "Who will be topscorer?")' },
    { value: 'top_n_ranking', label: 'Top N Ranking (e.g., "Top 3 Dutch parties")' },
];

// Balance feasibility warning
const membersUnderStake = computed(() => {
    if (!props.groupMembers || props.groupMembers.length === 0) return null;
    
    const under = props.groupMembers.filter(m => m.points < form.stake_amount);
    if (under.length === 0) return null;
    
    const names = under.slice(0, 2).map(m => m.name).join(', ');
    const others = under.length > 2 ? ` + ${under.length - 2} other${under.length - 2 > 1 ? 's' : ''}` : '';
    
    return {
        count: under.length,
        display: names + others
    };
});

const addOption = () => {
    form.options.push('');
};

const removeOption = (index: number) => {
    form.options.splice(index, 1);
};

// Auto-populate labels and reset thresholds when binary type changes
watch(() => form.type, (newType: string) => {
    if (newType === 'binary_yes_no') {
        form.label_option_a = 'Yes';
        form.label_option_b = 'No';
        form.threshold_value = null;
        form.threshold_date = null;
    } else if (newType === 'binary_over_under') {
        form.label_option_a = 'Over';
        form.label_option_b = 'Under';
        form.threshold_date = null;
    } else if (newType === 'binary_before_after') {
        form.label_option_a = 'Before';
        form.label_option_b = 'After';
        form.threshold_value = null;
    } else if (newType === 'binary_custom') {
        // Keep whatever user has set, but clear thresholds
        form.threshold_value = null;
        form.threshold_date = null;
    }
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

const submit = () => {
    // Clear previous errors and toasts
    showToast.value = false;

    form.post('/wager/store', {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Wager created successfully!';
            showToast.value = true;

            // Will redirect to dashboard via backend
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to create wager. Please check the form and try again.';
            showToast.value = true;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Create Wager" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
                    🎲 Create a New Wager
                </h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Combined User Info and Group -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">
                            Creating as <strong>{{ user.name }}</strong>
                            <span v-if="user.telegram_username" class="text-neutral-500">
                                (@{{ user.telegram_username }})
                            </span>
                            <span v-if="defaultGroup">
                                in <strong>{{ defaultGroup.name }}</strong>
                            </span>
                        </p>
                        <p v-if="defaultGroup" class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Wager will be created in the mentioned group above
                        </p>
                    </div>

                    <!-- Group Selection (only shown when not locked) -->
                    <div v-if="!defaultGroup">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Group *
                        </label>
                        <select
                            v-model="form.group_id"
                            required
                            :disabled="groups.length === 0"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <option value="" disabled>Select a group</option>
                            <option v-for="group in groups" :key="group.id" :value="group.id">
                                {{ group.telegram_chat_title || group.name }}
                            </option>
                        </select>
                        <p v-if="groups.length === 0" class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                            ⚠️ No groups available. Make sure BeatWager bot is added to your group first, then try /newwager from that group.
                        </p>
                        <p v-else class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Group not listed? Make sure BeatWager bot is part of that group first.
                        </p>
                        <FormError :error="form.errors.group_id" />
                    </div>

                    <!-- Wager Type -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Wager Type
                        </label>
                        <select 
                            v-model="form.type" 
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        >
                            <option v-for="type in wagerTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Question/Title *
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="e.g., Ajax vs PSV - Who wins?"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <FormError :error="form.errors.title" />
                    </div>

                    <!-- Binary Type: Yes/No -->
                    <div v-if="form.type === 'binary_yes_no'" class="space-y-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Simple Yes/No question. Bets will be on "Yes" or "No".
                            </p>
                        </div>
                    </div>

                    <!-- Binary Type: Over/Under -->
                    <div v-if="form.type === 'binary_over_under'" class="space-y-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Over/Under question. Bets will be on "Over" or "Under" the threshold.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Numeric Threshold *
                            </label>
                            <input
                                v-model.number="form.threshold_value"
                                type="number"
                                step="0.01"
                                required
                                placeholder="e.g., 2.5 (for over/under 2.5 goals)"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                The threshold value for automatic settlement (e.g., 2.5 goals, 100 points)
                            </p>
                            <FormError :error="form.errors.threshold_value" />
                        </div>
                    </div>

                    <!-- Binary Type: Before/After -->
                    <div v-if="form.type === 'binary_before_after'" class="space-y-4">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Before/After question. Bets will be on "Before" or "After" the threshold date.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Date Threshold *
                            </label>
                            <input
                                v-model="form.threshold_date"
                                type="date"
                                required
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                The threshold date for automatic settlement (e.g., Jan 1, 2025)
                            </p>
                            <FormError :error="form.errors.threshold_date" />
                        </div>
                    </div>

                    <!-- Binary Type: Custom -->
                    <div v-if="form.type === 'binary_custom'" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Option A Label *
                                </label>
                                <input
                                    v-model="form.label_option_a"
                                    type="text"
                                    required
                                    placeholder="e.g., Male, Sober, Home Win"
                                    maxlength="50"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <FormError :error="form.errors.label_option_a" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Option B Label *
                                </label>
                                <input
                                    v-model="form.label_option_b"
                                    type="text"
                                    required
                                    placeholder="e.g., Female, Drunk, Away Win"
                                    maxlength="50"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <FormError :error="form.errors.label_option_b" />
                            </div>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            💡 Create your own custom binary options (e.g., "Male/Female", "Sober/Drunk", "Ajax/PSV")
                        </p>
                    </div>

                    <!-- Options for Multiple Choice -->
                    <div v-if="form.type === 'multiple_choice'" class="space-y-2">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Options
                        </label>
                        <div v-for="(option, index) in form.options" :key="index" class="flex gap-2">
                            <input
                                v-model="form.options[index]"
                                type="text"
                                required
                                :placeholder="index === 0 ? '1' : index === 1 ? 'x' : index === 2 ? '2' : 'Option ' + (index + 1)"
                                class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <button
                                v-if="form.options.length > 2"
                                type="button"
                                @click="removeOption(index)"
                                class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            >
                                Remove
                            </button>
                        </div>
                        <button
                            type="button"
                            @click="addOption"
                            class="px-4 py-2 bg-neutral-200 dark:bg-neutral-600 text-neutral-900 dark:text-white rounded-md hover:bg-neutral-300 dark:hover:bg-neutral-500"
                        >
                            + Add Option
                        </button>
                    </div>

                    <!-- Short Answer: Max Length -->
                    <div v-if="form.type === 'short_answer'">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Maximum Answer Length (characters)
                        </label>
                        <input
                            v-model.number="form.max_length"
                            type="number"
                            required
                            min="10"
                            max="500"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Users will answer with free text (e.g., "Who will be the topscorer?")
                        </p>
                        <FormError :error="form.errors.max_length" />
                    </div>

                    <!-- Top N Ranking: Options and N -->
                    <div v-if="form.type === 'top_n_ranking'" class="space-y-4">
                        <!-- Options -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Ranking Options *
                            </label>
                            <div v-for="(option, index) in form.options" :key="index" class="flex gap-2">
                                <input
                                    v-model="form.options[index]"
                                    type="text"
                                    required
                                    :placeholder="`Option ${index + 1}`"
                                    class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <button
                                    v-if="form.options.length > form.n"
                                    type="button"
                                    @click="removeOption(index)"
                                    class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                >
                                    Remove
                                </button>
                            </div>
                            <button
                                type="button"
                                @click="addOption"
                                class="px-4 py-2 bg-neutral-200 dark:bg-neutral-600 text-neutral-900 dark:text-white rounded-md hover:bg-neutral-300 dark:hover:bg-neutral-500"
                            >
                                + Add Option
                            </button>
                        </div>

                        <!-- N value -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                How many to rank (N) *
                            </label>
                            <input
                                v-model.number="form.n"
                                type="number"
                                required
                                min="2"
                                :max="form.options.length"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Users will rank their top {{ form.n }} picks (e.g., "Top 3 Dutch political parties")
                            </p>
                            <FormError :error="form.errors.n" />
                        </div>
                    </div>

                    <!-- Numeric Type: Min/Max and Winner Type -->
                    <div v-if="form.type === 'numeric'" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Minimum Value (optional)
                                </label>
                                <input
                                    v-model.number="form.numeric_min"
                                    type="number"
                                    placeholder="e.g., 0"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <FormError :error="form.errors.numeric_min" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Maximum Value (optional)
                                </label>
                                <input
                                    v-model.number="form.numeric_max"
                                    type="number"
                                    placeholder="e.g., 100"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <FormError :error="form.errors.numeric_max" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Winner Selection
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        v-model="form.numeric_winner_type"
                                        type="radio"
                                        value="closest"
                                        class="text-blue-600"
                                    />
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                        Closest guess wins (most forgiving)
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        v-model="form.numeric_winner_type"
                                        type="radio"
                                        value="exact"
                                        class="text-blue-600"
                                    />
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                        Exact match only (must be perfect)
                                    </span>
                                </label>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                                Users will guess a number (e.g., "How many goals will be scored?")
                            </p>
                        </div>
                    </div>

                    <!-- Date Type: Min/Max and Winner Type -->
                    <div v-if="form.type === 'date'" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Earliest Date (optional)
                                </label>
                                <input
                                    v-model="form.date_min"
                                    type="date"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <FormError :error="form.errors.date_min" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Latest Date (optional)
                                </label>
                                <input
                                    v-model="form.date_max"
                                    type="date"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <FormError :error="form.errors.date_max" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Winner Selection
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        v-model="form.date_winner_type"
                                        type="radio"
                                        value="closest"
                                        class="text-blue-600"
                                    />
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                        Closest date wins (most forgiving)
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        v-model="form.date_winner_type"
                                        type="radio"
                                        value="exact"
                                        class="text-blue-600"
                                    />
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                        Exact date only (must be perfect)
                                    </span>
                                </label>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                                Users will predict a date (e.g., "When will X happen?")
                            </p>
                        </div>
                    </div>

                    <!-- Stake Amount -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Stake (points) *
                        </label>
                        <input
                            v-model.number="form.stake_amount"
                            type="number"
                            required
                            min="1"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <FormError :error="form.errors.stake_amount" />
                    </div>

                    <!-- Betting Closes At and Expected Settlement (combined on desktop) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Betting Closes At -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Betting Closes *
                            </label>
                            <input
                                v-model="form.betting_closes_at"
                                type="datetime-local"
                                required
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                When users can no longer place bets
                            </p>
                            <FormError :error="form.errors.betting_closes_at" />
                        </div>

                        <!-- Expected Settlement Date -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Expected Result Date (optional)
                            </label>
                            <input
                                v-model="form.expected_settlement_at"
                                type="datetime-local"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                When the outcome will be known (optional)
                            </p>
                            <FormError :error="form.errors.expected_settlement_at" />
                        </div>
                    </div>
                    <!-- Balance feasibility warning -->
                    <div v-if="membersUnderStake" class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded text-sm">
                        <p class="text-amber-700 dark:text-amber-300">
                            ⚠️ {{ membersUnderStake.display }} {{ membersUnderStake.count === 1 ? 'has a' : 'have' }} balance{{ membersUnderStake.count > 1 ? 's' : '' }} lower than this stake
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium inline-flex items-center justify-center gap-2"
                        >
                            <!-- Loading spinner -->
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Creating...' : 'Create Wager' }}</span>
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
