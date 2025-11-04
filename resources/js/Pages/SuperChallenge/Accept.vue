<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    challenge: {
        id: string;
        description: string;
        prize_per_person: number;
        max_participants: number;
        current_participants: number;
        deadline: string;
        evidence_guidance: string | null;
    };
    group: {
        name: string;
    };
    user: {
        id: string;
        name: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    confirmed: true,
});

const accept = () => {
    form.get(`/superchallenge/${props.challenge.id}/accept?confirmed=1`);
};
</script>

<template>
    <AppLayout>
        <Head title="Accept SuperChallenge" />

        <div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full mb-4">
                        <span class="text-3xl">🏆</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        SuperChallenge Invitation
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        From <span class="font-semibold text-purple-600 dark:text-purple-400">{{ group.name }}</span>
                    </p>
                </div>

                <!-- Challenge Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
                    <!-- Challenge Description -->
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                            The Challenge
                        </h2>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ challenge.description }}
                        </p>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <!-- Prize -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Your Prize</span>
                            </div>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ challenge.prize_per_person }} points
                            </p>
                        </div>

                        <!-- Deadline -->
                        <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Deadline</span>
                            </div>
                            <p class="text-lg font-bold text-orange-600 dark:text-orange-400">
                                {{ challenge.deadline }}
                            </p>
                        </div>
                    </div>

                    <!-- Participants -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Participants</span>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ challenge.current_participants }}
                                </span>
                                <span class="text-lg text-gray-500 dark:text-gray-400">
                                    / {{ challenge.max_participants }}
                                </span>
                            </div>
                        </div>
                        <!-- Progress Bar -->
                        <div class="mt-3 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-blue-500 to-indigo-500 h-full transition-all duration-500"
                                :style="{ width: `${(challenge.current_participants / challenge.max_participants) * 100}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Evidence Guidance -->
                    <div v-if="challenge.evidence_guidance" class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-purple-900 dark:text-purple-200 mb-1">
                                    How to prove completion:
                                </p>
                                <p class="text-sm text-purple-800 dark:text-purple-300">
                                    {{ challenge.evidence_guidance }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- How It Works -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            How it works:
                        </h3>
                        <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <span class="font-bold text-purple-600 dark:text-purple-400">1.</span>
                                <span>Accept the challenge and work towards completing it</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold text-purple-600 dark:text-purple-400">2.</span>
                                <span>When done, claim completion in the chat</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold text-purple-600 dark:text-purple-400">3.</span>
                                <span>The creator validates your completion</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold text-purple-600 dark:text-purple-400">4.</span>
                                <span>Receive your {{ challenge.prize_per_person }} points!</span>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Action Button -->
                <button
                    @click="accept"
                    :disabled="form.processing"
                    class="w-full py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    {{ form.processing ? 'Accepting...' : 'Accept Challenge' }}
                </button>

                <!-- Info Note -->
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                    You can complete this challenge anytime before {{ challenge.deadline }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
