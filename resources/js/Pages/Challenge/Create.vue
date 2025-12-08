<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';
import FormError from '@/Components/FormError.vue';

const props = defineProps<{
    user: {
        id: string;
        name: string;
        telegram_username?: string;
        balance?: number | null;
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
    eliminationDefaults?: {
        suggested_pot: number;
        suggested_buy_in: number;
        total_group_currency: number;
        currency_name: string;
        season_ends_at?: string | null;
    };
    eliminationMinParticipants?: number;
}>();

const minParticipants = computed(() => props.eliminationMinParticipants ?? 3);

type ChallengeKind = 'user_challenge' | 'elimination_challenge';
type EliminationMode = 'last_man_standing' | 'deadline';

const challengeKind = ref<ChallengeKind>('user_challenge');

const form = useForm({
    description: '',
    amount: 100,
    is_offering_service: false, // false = Type 1 (I need help), true = Type 2 (I want to help)
    group_id: props.defaultGroup?.id || '',
    completion_deadline: '',
    acceptance_deadline: '',
    // Elimination challenge fields
    challenge_type: 'user_challenge' as ChallengeKind,
    elimination_trigger: '',
    elimination_mode: 'deadline' as EliminationMode,
    tap_in_deadline: '',
    min_participants: props.eliminationMinParticipants ?? 3,
    point_pot: 0,
});

// Sync challengeKind with form field
watch(challengeKind, (newKind) => {
    form.challenge_type = newKind;
    // Reset mode-specific fields when switching
    if (newKind === 'user_challenge') {
        form.elimination_trigger = '';
        form.elimination_mode = 'deadline';
        form.tap_in_deadline = '';
        form.min_participants = minParticipants.value;
        form.point_pot = 0;
    } else {
        // Initialize with suggested pot for elimination challenges
        form.point_pot = suggestedPot.value;

        // If group has active season, default deadline to season end
        if (props.eliminationDefaults?.season_ends_at) {
            const seasonEnd = new Date(props.eliminationDefaults.season_ends_at);
            form.completion_deadline = seasonEnd.toISOString().slice(0, 16);
        }
    }
});

// Computed suggested pot and buy-in
const suggestedPot = computed(() => {
    if (props.eliminationDefaults) {
        return props.eliminationDefaults.suggested_pot;
    }
    // Fallback: 10% of group points (estimated)
    const totalPoints = props.groupMembers.reduce((sum, m) => sum + m.points, 0);
    return Math.floor(totalPoints * 0.1);
});

const suggestedBuyIn = computed(() => {
    if (props.eliminationDefaults) {
        return props.eliminationDefaults.suggested_buy_in;
    }
    // Fallback: 50% of fair share
    const memberCount = Math.max(props.groupMembers.length, 3);
    return Math.floor((suggestedPot.value / memberCount) * 0.5);
});

// Dynamic buy-in based on user's pot input
const calculatedBuyIn = computed(() => {
    const pot = form.point_pot || 0;
    const memberCount = Math.max(props.groupMembers.length, 3);
    return Math.floor((pot / memberCount) * 0.5);
});

const currencyName = computed(() => {
    return props.eliminationDefaults?.currency_name || 'points';
});

// Check if user can afford the calculated buy-in
const canAffordBuyIn = computed(() => {
    if (props.user.balance === null || props.user.balance === undefined) return true;
    return props.user.balance >= calculatedBuyIn.value;
});

// Balance feasibility warning
const membersUnderAmount = computed(() => {
    if (!props.groupMembers || props.groupMembers.length === 0) return null;

    const under = props.groupMembers.filter(m => m.points < form.amount);
    if (under.length === 0) return null;

    const names = under.slice(0, 2).map(m => m.name).join(', ');
    const others = under.length > 2 ? ` + ${under.length - 2} other${under.length - 2 > 1 ? 's' : ''}` : '';

    return {
        count: under.length,
        display: names + others
    };
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Set default completion deadline to 3 days from now
const setDefaultCompletionDeadline = () => {
    const date = new Date();
    date.setDate(date.getDate() + 3);
    form.completion_deadline = date.toISOString().slice(0, 16);
};

// Set completion deadline to 7 days from now (for elimination)
const setWeekDeadline = () => {
    const date = new Date();
    date.setDate(date.getDate() + 7);
    form.completion_deadline = date.toISOString().slice(0, 16);
};

// Set completion deadline to 30 days from now (for elimination)
const setMonthDeadline = () => {
    const date = new Date();
    date.setDate(date.getDate() + 30);
    form.completion_deadline = date.toISOString().slice(0, 16);
};

// Set default acceptance deadline to 1 day from now
const setDefaultAcceptanceDeadline = () => {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    form.acceptance_deadline = date.toISOString().slice(0, 16);
};

// Set tap-in deadline to 24 hours from now
const setTapInDeadline24h = () => {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    form.tap_in_deadline = date.toISOString().slice(0, 16);
};

// Set tap-in deadline to 48 hours from now
const setTapInDeadline48h = () => {
    const date = new Date();
    date.setDate(date.getDate() + 2);
    form.tap_in_deadline = date.toISOString().slice(0, 16);
};

const clearAcceptanceDeadline = () => {
    form.acceptance_deadline = '';
};

const clearTapInDeadline = () => {
    form.tap_in_deadline = '';
};

const submit = () => {
    showToast.value = false;

    if (challengeKind.value === 'elimination_challenge') {
        // Submit to elimination endpoint
        form.post('/challenges/store-elimination', {
            onSuccess: () => {
                toastType.value = 'success';
                toastMessage.value = 'Elimination challenge created!';
                showToast.value = true;
            },
            onError: (errors) => {
                toastType.value = 'error';
                toastMessage.value = 'Failed to create challenge. Please check the form.';
                showToast.value = true;
            },
        });
    } else {
        // Transform amount to negative if offering service (Type 2)
        form.transform((data) => ({
            ...data,
            amount: data.is_offering_service ? -Math.abs(data.amount) : Math.abs(data.amount),
            is_offering_service: undefined,
            // Remove elimination fields for user challenges
            challenge_type: undefined,
            elimination_trigger: undefined,
            elimination_mode: undefined,
            tap_in_deadline: undefined,
            min_participants: undefined,
        })).post('/challenges/store', {
            onSuccess: () => {
                toastType.value = 'success';
                toastMessage.value = 'Challenge created successfully!';
                showToast.value = true;
            },
            onError: (errors) => {
                toastType.value = 'error';
                toastMessage.value = 'Failed to create challenge. Please check the form and try again.';
                showToast.value = true;
            },
        });
    }
};

// Elimination challenge examples
const eliminationExamples = [
    { trigger: "Hearing 'Last Christmas' by Wham!", description: "Classic holiday avoidance challenge" },
    { trigger: "Eating fast food", description: "Health-focused survival" },
    { trigger: "Being late to a meeting", description: "Punctuality challenge" },
    { trigger: "Using the word 'like' as a filler", description: "Speaking challenge" },
    { trigger: "Checking social media before noon", description: "Digital detox" },
];
</script>

<template>
    <AppLayout>
        <Head title="Create Challenge" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
                    💪 Create a New Challenge
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
                            Challenge will be created in the mentioned group above
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
                            No groups available. Make sure BeatWager bot is added to your group first, then try /newchallenge from that group.
                        </p>
                        <p v-else class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Group not listed? Make sure BeatWager bot is part of that group first.
                        </p>
                        <FormError :error="form.errors.group_id" />
                    </div>

                    <!-- Challenge Kind Selection (NEW) -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                            Challenge Format *
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all"
                                   :class="challengeKind === 'user_challenge'
                                       ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                       : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'">
                                <input
                                    type="radio"
                                    v-model="challengeKind"
                                    value="user_challenge"
                                    class="mt-1 mr-3"
                                />
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-white">
                                        🤝 1-on-1 Challenge
                                    </div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                        One person accepts and completes a task for points
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all"
                                   :class="challengeKind === 'elimination_challenge'
                                       ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                       : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'">
                                <input
                                    type="radio"
                                    v-model="challengeKind"
                                    value="elimination_challenge"
                                    class="mt-1 mr-3"
                                />
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-white">
                                        🎯 Elimination Challenge
                                    </div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                        Survival game - avoid the trigger or get eliminated!
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- USER CHALLENGE FIELDS -->
                    <!-- ============================================ -->
                    <template v-if="challengeKind === 'user_challenge'">
                        <!-- Challenge Type Selection -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                                What type of challenge? *
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="!form.is_offering_service
                                           ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                           : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'">
                                    <input
                                        type="radio"
                                        v-model="form.is_offering_service"
                                        :value="false"
                                        class="mt-1 mr-3"
                                    />
                                    <div>
                                        <div class="font-medium text-neutral-900 dark:text-white">
                                            🫳 I need help (I'll pay)
                                        </div>
                                        <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                            You'll pay points to whoever completes the task
                                        </div>
                                    </div>
                                </label>

                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="form.is_offering_service
                                           ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                           : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'">
                                    <input
                                        type="radio"
                                        v-model="form.is_offering_service"
                                        :value="true"
                                        class="mt-1 mr-3"
                                    />
                                    <div>
                                        <div class="font-medium text-neutral-900 dark:text-white">
                                            🫴 I want to help (I need points)
                                        </div>
                                        <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                            You'll receive points from whoever accepts your offer
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Challenge Description *
                            </label>
                            <textarea
                                v-model="form.description"
                                required
                                rows="3"
                                :placeholder="form.is_offering_service
                                    ? 'e.g., I\'ll clean the office kitchen this Friday for 200 points!'
                                    : 'e.g., Who will clean the office kitchen by Friday? I\'ll pay 200 points for this to get done!'"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            ></textarea>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                {{ form.is_offering_service ? 'Describe what you\'ll do to earn the points' : 'Describe what needs to be done to earn the points' }}
                            </p>
                            <FormError :error="form.errors.description" />
                        </div>

                        <!-- Points Amount -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ form.is_offering_service ? 'Points You\'ll Earn *' : 'Points Reward *' }}
                            </label>
                            <input
                                v-model.number="form.amount"
                                type="number"
                                required
                                min="1"
                                max="10000"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                {{ form.is_offering_service
                                    ? 'Points you\'ll receive from whoever accepts this challenge'
                                    : 'Points you\'ll pay to whoever completes this challenge' }}
                            </p>
                            <FormError :error="form.errors.amount" />
                        </div>

                        <!-- Deadlines -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Completion Deadline -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Must Complete By *
                                </label>
                                <div class="space-y-2">
                                    <input
                                        v-model="form.completion_deadline"
                                        type="datetime-local"
                                        required
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                    />
                                    <button
                                        type="button"
                                        @click="setDefaultCompletionDeadline"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        Set to 3 days from now
                                    </button>
                                </div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    When the challenge must be completed by
                                </p>
                                <FormError :error="form.errors.completion_deadline" />
                            </div>

                            <!-- Acceptance Deadline -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Accept By (optional)
                                </label>
                                <div class="space-y-2">
                                    <input
                                        v-model="form.acceptance_deadline"
                                        type="datetime-local"
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                    />
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="setDefaultAcceptanceDeadline"
                                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                        >
                                            Set to 1 day from now
                                        </button>
                                        <button
                                            v-if="form.acceptance_deadline"
                                            type="button"
                                            @click="clearAcceptanceDeadline"
                                            class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    Deadline for someone to accept the challenge
                                </p>
                                <FormError :error="form.errors.acceptance_deadline" />
                            </div>
                        </div>

                        <!-- Balance feasibility warning -->
                        <div v-if="membersUnderAmount" class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded text-sm">
                            <p class="text-amber-700 dark:text-amber-300">
                                Note: {{ membersUnderAmount.display }} {{ membersUnderAmount.count === 1 ? 'has a' : 'have' }} balance{{ membersUnderAmount.count > 1 ? 's' : '' }} lower than your reward amount, but can still accept the challenge
                            </p>
                        </div>

                        <!-- How it works info -->
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">How it works:</h3>
                            <ul v-if="!form.is_offering_service" class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                <li>1. One person from your group can accept this challenge</li>
                                <li>2. Your points will be held in reserve when accepted</li>
                                <li>3. They complete the task and submit proof</li>
                                <li>4. You approve or reject their submission</li>
                                <li>5. If approved, they get your points!</li>
                            </ul>
                            <ul v-else class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                <li>1. One person from your group can accept your offer</li>
                                <li>2. Their points will be held in reserve when accepted</li>
                                <li>3. You complete the task and submit proof</li>
                                <li>4. They approve or reject your submission</li>
                                <li>5. If approved, you get their points!</li>
                            </ul>
                        </div>
                    </template>

                    <!-- ============================================ -->
                    <!-- ELIMINATION CHALLENGE FIELDS -->
                    <!-- ============================================ -->
                    <template v-else>
                        <!-- Elimination Trigger -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Elimination Trigger *
                            </label>
                            <textarea
                                v-model="form.elimination_trigger"
                                required
                                rows="2"
                                placeholder="e.g., Hearing 'Last Christmas' by Wham!"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            ></textarea>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                What action or event eliminates a participant?
                            </p>
                            <FormError :error="form.errors.elimination_trigger" />

                            <!-- Example triggers -->
                            <div class="mt-3">
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">Examples:</p>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="example in eliminationExamples"
                                        :key="example.trigger"
                                        type="button"
                                        @click="form.elimination_trigger = example.trigger"
                                        class="text-xs px-2 py-1 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 rounded text-neutral-700 dark:text-neutral-300 transition-colors"
                                    >
                                        {{ example.trigger }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Challenge Name/Description -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Challenge Name *
                            </label>
                            <input
                                v-model="form.description"
                                type="text"
                                required
                                placeholder="e.g., The Wham! Christmas Challenge"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                A catchy name for your elimination challenge
                            </p>
                            <FormError :error="form.errors.description" />
                        </div>

                        <!-- Elimination Mode -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                                How does it end? *
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="form.elimination_mode === 'last_man_standing'
                                           ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                           : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'">
                                    <input
                                        type="radio"
                                        v-model="form.elimination_mode"
                                        value="last_man_standing"
                                        class="mt-1 mr-3"
                                    />
                                    <div>
                                        <div class="font-medium text-neutral-900 dark:text-white">
                                            🏆 Last One Standing
                                        </div>
                                        <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                            Continues until only one survivor remains
                                        </div>
                                    </div>
                                </label>

                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="form.elimination_mode === 'deadline'
                                           ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                           : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'">
                                    <input
                                        type="radio"
                                        v-model="form.elimination_mode"
                                        value="deadline"
                                        class="mt-1 mr-3"
                                    />
                                    <div>
                                        <div class="font-medium text-neutral-900 dark:text-white">
                                            ⏰ Deadline Mode
                                        </div>
                                        <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                            All survivors at deadline split the pot
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <FormError :error="form.errors.elimination_mode" />
                        </div>

                        <!-- Deadline (for deadline mode) -->
                        <div v-if="form.elimination_mode === 'deadline'">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Challenge Ends *
                            </label>
                            <div class="space-y-2">
                                <input
                                    v-model="form.completion_deadline"
                                    type="datetime-local"
                                    required
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="setWeekDeadline"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        1 week
                                    </button>
                                    <button
                                        type="button"
                                        @click="setMonthDeadline"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        1 month
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                All remaining survivors at this time split the pot
                            </p>
                            <p v-if="props.eliminationDefaults?.season_ends_at" class="text-xs text-green-600 dark:text-green-400 mt-1">
                                ✨ Defaulted to season end date
                            </p>
                            <FormError :error="form.errors.completion_deadline" />
                        </div>

                        <!-- Tap-In Deadline -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Tap-In Deadline (optional)
                            </label>
                            <div class="space-y-2">
                                <input
                                    v-model="form.tap_in_deadline"
                                    type="datetime-local"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="setTapInDeadline24h"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        24 hours
                                    </button>
                                    <button
                                        type="button"
                                        @click="setTapInDeadline48h"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        48 hours
                                    </button>
                                    <button
                                        v-if="form.tap_in_deadline"
                                        type="button"
                                        @click="clearTapInDeadline"
                                        class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Last chance for participants to join (leave empty for open-ended)
                            </p>
                            <FormError :error="form.errors.tap_in_deadline" />
                        </div>

                        <!-- Minimum Participants -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Minimum Participants
                            </label>
                            <input
                                v-model.number="form.min_participants"
                                type="number"
                                :min="minParticipants"
                                max="50"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Challenge auto-cancels if fewer than this many join by tap-in deadline
                            </p>
                            <FormError :error="form.errors.min_participants" />
                        </div>

                        <!-- Prize Pot -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Prize Pot ({{ currencyName }})
                            </label>
                            <input
                                v-model.number="form.point_pot"
                                type="number"
                                min="0"
                                :placeholder="suggestedPot.toString()"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Suggested: {{ suggestedPot.toLocaleString() }} {{ currencyName }} (10% of group total)
                            </p>
                            <FormError :error="form.errors.point_pot" />
                        </div>

                        <!-- Prize Info Box -->
                        <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                            <h3 class="font-semibold text-orange-900 dark:text-orange-100 mb-2">Prize Pool Info</h3>
                            <ul class="text-sm text-orange-800 dark:text-orange-200 space-y-1">
                                <li><strong>Current Pot:</strong> {{ (form.point_pot || 0).toLocaleString() }} {{ currencyName }}</li>
                                <li><strong>Est. Buy-in:</strong> ~{{ calculatedBuyIn.toLocaleString() }} {{ currencyName }} per participant</li>
                                <li><strong>System match:</strong> The house covers the difference!</li>
                            </ul>
                            <p class="text-xs text-orange-700 dark:text-orange-300 mt-2">
                                Buy-in is 50% of (pot ÷ group size) - adjusted based on your pot value
                            </p>
                        </div>

                        <!-- Buy-in affordability warning -->
                        <div v-if="!canAffordBuyIn && user.balance !== null" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <div class="flex items-start gap-2">
                                <span class="text-lg">⚠️</span>
                                <div class="text-sm text-red-700 dark:text-red-300">
                                    <p class="font-medium">You may not be able to afford the buy-in!</p>
                                    <p class="mt-1">
                                        Your balance: <strong>{{ user.balance?.toLocaleString() }} {{ currencyName }}</strong><br>
                                        Estimated buy-in: <strong>{{ calculatedBuyIn.toLocaleString() }} {{ currencyName }}</strong>
                                    </p>
                                    <p class="text-xs mt-2 text-red-600 dark:text-red-400">
                                        You can still create this challenge, but won't be able to tap in yourself.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- How it works info -->
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">How it works:</h3>
                            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                <li>1. Group members "tap in" with a buy-in to join</li>
                                <li>2. If someone triggers the condition, they "tap out" (honor system)</li>
                                <li>3. Eliminated participants' buy-ins go to the pot</li>
                                <li v-if="form.elimination_mode === 'last_man_standing'">
                                    4. Last survivor takes the entire pot!
                                </li>
                                <li v-else>
                                    4. All survivors at the deadline split the pot equally
                                </li>
                            </ul>
                        </div>
                    </template>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 px-6 py-3 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed font-medium inline-flex items-center justify-center gap-2 transition-colors"
                            :class="challengeKind === 'elimination_challenge'
                                ? 'bg-orange-600 hover:bg-orange-700'
                                : 'bg-purple-600 hover:bg-purple-700'"
                        >
                            <!-- Loading spinner -->
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing
                                ? 'Creating...'
                                : (challengeKind === 'elimination_challenge' ? 'Create Elimination Challenge' : 'Create Challenge')
                            }}</span>
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
