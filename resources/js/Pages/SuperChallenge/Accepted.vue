<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    challenge: {
        description: string;
        prize_per_person: number;
        deadline: string;
    };
}

const props = defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Challenge Accepted!" />

        <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-xl mx-auto">
                <div class="text-center mb-8">
                    <!-- Success Icon -->
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full mb-6">
                        <span class="text-5xl">💪</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        You're In!
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">
                        Challenge accepted. Now go crush it!
                    </p>
                </div>

                <!-- Challenge Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
                    <!-- Challenge Description -->
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                            Your Mission
                        </h2>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ challenge.description }}
                        </p>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <!-- Prize -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4 text-center">
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                You'll Win
                            </div>
                            <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                                {{ challenge.prize_per_person }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                points
                            </div>
                        </div>

                        <!-- Deadline -->
                        <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg p-4 text-center">
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Deadline
                            </div>
                            <div class="text-lg font-bold text-orange-600 dark:text-orange-400">
                                {{ challenge.deadline }}
                            </div>
                        </div>
                    </div>

                    <!-- Separator -->
                    <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>

                    <!-- Next Steps -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Next Steps:
                        </h3>
                        <ol class="space-y-3">
                            <li class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">1</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                        Complete the challenge
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Do it anytime before the deadline
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">2</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                        Claim completion in Telegram
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        You'll get a link to submit your claim
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">3</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                        Post your evidence
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Share proof in the group chat (photo, screenshot, etc.)
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">4</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                                        Get validated & win points!
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        The creator validates and you receive {{ challenge.prize_per_person }} points
                                    </p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Motivational Note -->
                <div class="bg-gradient-to-r from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-xl p-6 text-center">
                    <p class="text-lg font-semibold text-purple-900 dark:text-purple-200 mb-2">
                        Remember: This is collaborative!
                    </p>
                    <p class="text-sm text-purple-800 dark:text-purple-300">
                        Everyone who completes the challenge gets the full prize. You're not competing against each other.
                    </p>
                </div>

                <!-- Close Message -->
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
                    You can close this window and return to Telegram
                </p>
            </div>
        </div>
    </AppLayout>
</template>
