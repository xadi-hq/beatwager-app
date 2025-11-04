<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Example {
    category: string;
    icon: string;
    examples: string[];
}

interface PrizeRange {
    min: number;
    max: number;
    step: number;
    suggested: number;
}

interface ParticipantRange {
    min: number;
    max: number;
    suggested: number;
}

interface CreatorRewards {
    base_acceptance_bonus: number;
    per_validation_bonus: number;
    dynamic_bonus_formula: {
        description: string;
        examples: Array<{ prize: number; bonus: number }>;
    };
}

const props = defineProps<{
    user: {
        id: string;
        name: string;
    };
    group: {
        id: string;
        name: string;
        member_count: number;
    };
    nudge_id: string;
    prize_range: PrizeRange;
    participant_range: ParticipantRange;
    creator_rewards: CreatorRewards;
    static_examples: Example[];
    currencyName: string;
}>();

// Use the currency name from props with fallback
const displayCurrency = computed(() => props.currencyName || 'points');

const form = useForm({
    nudge_id: props.nudge_id,
    description: '',
    deadline_days: 7,
    prize_per_person: props.prize_range.suggested,
    max_participants: props.participant_range.suggested,
    evidence_guidance: '',
});

// Computed: Dynamic creator acceptance bonus
const creatorAcceptanceBonus = computed(() => {
    return (props.prize_range.max - form.prize_per_person) + props.creator_rewards.base_acceptance_bonus;
});

// Computed: Potential total if all participants complete
const potentialTotalReward = computed(() => {
    return creatorAcceptanceBonus.value + (form.max_participants * props.creator_rewards.per_validation_bonus);
});

// Computed: Total prize pool
const totalPrizePool = computed(() => {
    return form.prize_per_person * form.max_participants;
});

// Computed: Urgency label based on max participants
const urgencyLabel = computed(() => {
    if (form.max_participants === 0) return null;
    if (form.max_participants === 1) return { text: 'Exclusive 1-on-1 challenge', icon: '👑' };
    if (form.max_participants <= 3) return { text: 'Limited spots - High urgency', icon: '⚡' };
    return { text: 'Open to many', icon: '🌟' };
});

// Use example
const useExample = (example: string) => {
    form.description = example;
};

// Random example picker
const pickRandomExample = () => {
    const allExamples = props.static_examples.flatMap(cat => cat.examples);
    const randomIndex = Math.floor(Math.random() * allExamples.length);
    form.description = allExamples[randomIndex];
};

const submit = () => {
    form.post('/superchallenge/create');
};
</script>

<template>
    <AppLayout>
        <Head title="Create SuperChallenge" />

        <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div>
                <!-- Hero Section -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full mb-4">
                        <span class="text-3xl">🏆</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        You've Been Chosen!
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        Create a SuperChallenge for <span class="font-semibold text-purple-600 dark:text-purple-400">{{ group.name }}</span>
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 space-y-8">
                    <!-- Challenge Description -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="description" class="block text-sm font-semibold text-gray-900 dark:text-white">
                                Challenge Description *
                            </label>
                            <button
                                type="button"
                                @click="pickRandomExample"
                                class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-medium flex items-center gap-1"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Random Idea
                            </button>
                        </div>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            maxlength="200"
                            required
                            placeholder="What should your group accomplish?"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition"
                        />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ form.description.length }}/200 characters
                        </p>
                        <div v-if="form.errors.description" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <!-- Prize Per Person Slider -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            Prize Per Person *
                        </label>
                        <div class="flex items-center gap-4">
                            <input
                                type="range"
                                v-model.number="form.prize_per_person"
                                :min="prize_range.min"
                                :max="prize_range.max"
                                :step="prize_range.step"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-purple-600"
                            />
                            <div class="flex items-center justify-center min-w-[100px] px-4 py-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                <span class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ form.prize_per_person }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">{{ displayCurrency }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ prize_range.min }} {{ displayCurrency }}</span>
                            <span>{{ prize_range.max }} {{ displayCurrency }}</span>
                        </div>
                    </div>

                    <!-- Max Participants Slider -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            Max Participants *
                        </label>
                        <div class="flex items-center gap-4">
                            <input
                                type="range"
                                v-model.number="form.max_participants"
                                :min="participant_range.min"
                                :max="participant_range.max"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-indigo-600"
                            />
                            <div class="flex items-center justify-center min-w-[100px] px-4 py-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                                <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ form.max_participants }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-1">{{ form.max_participants === 1 ? 'person' : 'people' }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ participant_range.min }}</span>
                            <span>{{ participant_range.max }}</span>
                        </div>
                        <div v-if="urgencyLabel" class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg text-sm text-indigo-700 dark:text-indigo-300">
                            <span>{{ urgencyLabel.icon }}</span>
                            <span>{{ urgencyLabel.text }}</span>
                        </div>
                        <div v-if="form.errors.max_participants" class="mt-2 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.max_participants }}
                        </div>
                    </div>

                    <!-- Creator Rewards Preview -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-6 border border-green-200 dark:border-green-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="text-2xl">💰</span>
                            Reward Breakdown
                        </h3>
                        <div class="space-y-4">
                            <!-- Their Bonus -->
                            <div>
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                    Their Bonus (per completer)
                                </div>
                                <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                    <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <span class="text-lg">👥</span>
                                        <span v-if="form.max_participants > 0">
                                            {{ form.max_participants }} × {{ form.prize_per_person }} {{ displayCurrency }}
                                        </span>
                                        <span v-else>
                                            0 × {{ form.prize_per_person }} {{ displayCurrency }}
                                        </span>
                                    </div>
                                    <span class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ totalPrizePool }} {{ displayCurrency }}</span>
                                </div>
                            </div>

                            <!-- Your Bonuses -->
                            <div>
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                    Your Bonuses
                                </div>
                                <div class="space-y-2">
                                    <!-- Fixed Acceptance Bonus -->
                                    <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">Fixed bonus</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">(when ≥1 joins)</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ creatorAcceptanceBonus }} {{ displayCurrency }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">(150 - {{ form.prize_per_person }}) + 50</div>
                                        </div>
                                    </div>
                                    <!-- Per Completion Bonus -->
                                    <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">Per validation</span>
                                        </div>
                                        <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ creator_rewards.per_validation_bonus }} {{ displayCurrency }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total if All Complete -->
                            <div v-if="form.max_participants > 0" class="pt-3 border-t border-green-200 dark:border-green-800">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Your total if all {{ form.max_participants }} complete:</span>
                                    <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ potentialTotalReward }} {{ displayCurrency }}</span>
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-xs text-gray-600 dark:text-gray-400 italic">
                            💡 Their prize ↑ = Your fixed bonus ↓ (but more likely to attract participants!)
                        </p>
                    </div>

                    <!-- Deadline -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            Deadline *
                        </label>
                        <div class="grid grid-cols-4 gap-3">
                            <label
                                v-for="days in [7, 14, 30, 60]"
                                :key="days"
                                class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition"
                                :class="form.deadline_days === days
                                    ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                    : 'border-gray-300 dark:border-gray-600 hover:border-purple-300'"
                            >
                                <input
                                    type="radio"
                                    v-model="form.deadline_days"
                                    :value="days"
                                    class="sr-only"
                                />
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ days }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">days</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Evidence Guidance (Optional) -->
                    <div>
                        <label for="evidence_guidance" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                            How should people prove completion? <span class="font-normal text-gray-500">(Optional)</span>
                        </label>
                        <input
                            id="evidence_guidance"
                            v-model="form.evidence_guidance"
                            type="text"
                            maxlength="500"
                            placeholder='e.g., "Screenshot from Strava app" or "Photo of completed project"'
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition"
                        />
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing || !form.description"
                        class="w-full py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        {{ form.processing ? 'Creating...' : 'Create SuperChallenge' }}
                    </button>
                </form>

                <!-- Static Examples -->
                <div class="mt-12">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 text-center">
                        Need inspiration? Popular SuperChallenges:
                    </h2>
                    <div class="space-y-6">
                        <div
                            v-for="category in static_examples"
                            :key="category.category"
                            class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md"
                        >
                            <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                <span class="text-2xl">{{ category.icon }}</span>
                                {{ category.category }}
                            </h3>
                            <div class="grid gap-2">
                                <button
                                    v-for="(example, index) in category.examples"
                                    :key="index"
                                    type="button"
                                    @click="useExample(example)"
                                    class="text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-700 dark:hover:text-purple-300 rounded-lg transition"
                                >
                                    • {{ example }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
