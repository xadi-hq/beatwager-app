<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    challenge: {
        id: string;
        description: string;
        prize_per_person: number;
        max_participants: number;
        deadline: string;
    };
    group: {
        name: string;
    };
}

const props = defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="SuperChallenge Created!" />

        <div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <div class="text-center mb-8">
                    <!-- Success Icon -->
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full mb-6 animate-bounce">
                        <span class="text-5xl">🎉</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        SuperChallenge Created!
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">
                        Your challenge has been announced to <span class="font-semibold text-purple-600 dark:text-purple-400">{{ group.name }}</span>
                    </p>
                </div>

                <!-- Challenge Summary Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
                    <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">
                        Challenge Summary
                    </h2>

                    <!-- Description -->
                    <div class="mb-6">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ challenge.description }}
                        </p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <!-- Prize -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4 text-center">
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Prize
                            </div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ challenge.prize_per_person }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                points each
                            </div>
                        </div>

                        <!-- Max Participants -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 text-center">
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                                Max
                            </div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ challenge.max_participants }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                participants
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

                    <!-- What's Next -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                            What happens next:
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">1</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Your group members will see the challenge in Telegram and can accept it
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">2</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        When participants complete it, they'll post evidence in the chat
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">3</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        You'll validate their completions and they'll receive their points
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Creator Bonus Info -->
                <div class="bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-lg p-6 mb-6">
                    <div class="flex items-start gap-4">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-2">
                                Your Creator Rewards
                            </p>
                            <ul class="text-sm text-yellow-800 dark:text-yellow-300 space-y-1">
                                <li>• <span class="font-semibold">+50 points</span> when the first person accepts</li>
                                <li>• <span class="font-semibold">+25 points</span> for each completion you validate</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Close Message -->
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    You can close this window and return to Telegram to see the announcement
                </p>
            </div>
        </div>
    </AppLayout>
</template>
