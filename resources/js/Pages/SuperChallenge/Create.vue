<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Example {
    category: string;
    icon: string;
    examples: string[];
}

const props = defineProps<{
    user: {
        id: string;
        name: string;
    };
    group: {
        id: string;
        name: string;
    };
    nudge_id: string;
    prize_per_person: number;
    max_participants: number;
    static_examples: Example[];
}>();

const form = useForm({
    nudge_id: props.nudge_id,
    description: '',
    deadline_days: 7,
    evidence_guidance: '',
});

const useExample = (example: string) => {
    form.description = example;
};

const submit = () => {
    form.post('/superchallenge/create');
};
</script>

<template>
    <AppLayout>
        <Head title="Create SuperChallenge" />

        <div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
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
                    <div class="mt-4 inline-flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ prize_per_person }} points per completer</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Max {{ max_participants }} participants</span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 space-y-6">
                    <!-- Challenge Description -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                            Challenge Description *
                        </label>
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

                    <!-- Deadline -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                            Deadline *
                        </label>
                        <div class="grid grid-cols-4 gap-3">
                            <label
                                v-for="days in [3, 7, 14, 30]"
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
                                    <div class="text-xs text-gray-500 dark:text-gray-400">day{{ days > 1 ? 's' : '' }}</div>
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
