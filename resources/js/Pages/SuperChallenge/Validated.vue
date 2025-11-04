<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    participant: {
        user_name: string;
    };
    approved: boolean;
    prize: number;
}

const props = defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head :title="approved ? 'Completion Approved' : 'Completion Rejected'" />

        <div
            class="min-h-screen py-12 px-4 sm:px-6 lg:px-8"
            :class="approved
                ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-900 dark:to-gray-800'
                : 'bg-gradient-to-br from-orange-50 to-red-50 dark:from-gray-900 dark:to-gray-800'"
        >
            <div class="max-w-xl mx-auto">
                <div class="text-center mb-8">
                    <!-- Icon -->
                    <div
                        class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-6"
                        :class="approved
                            ? 'bg-gradient-to-br from-green-500 to-emerald-600 animate-bounce'
                            : 'bg-gradient-to-br from-orange-500 to-red-600'"
                    >
                        <span class="text-5xl">{{ approved ? '✅' : '❌' }}</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ approved ? 'Completion Approved!' : 'Completion Rejected' }}
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">
                        <span v-if="approved">
                            {{ participant.user_name }} has been awarded their points
                        </span>
                        <span v-else>
                            {{ participant.user_name }}'s completion was not approved
                        </span>
                    </p>
                </div>

                <!-- Result Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
                    <!-- Approval Content -->
                    <div v-if="approved">
                        <!-- Prize Awarded -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-6 mb-6 text-center">
                            <div class="flex items-center justify-center gap-3 mb-2">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                        Prize Awarded
                                    </p>
                                    <p class="text-4xl font-bold text-green-600 dark:text-green-400">
                                        {{ prize }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        points
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Your Bonus -->
                        <div class="bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-lg p-5 mb-6">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-1">
                                        Your Validation Bonus
                                    </p>
                                    <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                        You've received <span class="font-bold">+25 points</span> for validating this completion!
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Success Message -->
                        <div class="text-center">
                            <p class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                Great job validating!
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ participant.user_name }} has been notified and their points have been added to their balance.
                            </p>
                        </div>
                    </div>

                    <!-- Rejection Content -->
                    <div v-else>
                        <!-- Rejection Notice -->
                        <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg p-6 mb-6 text-center">
                            <svg class="w-16 h-16 text-red-600 dark:text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-lg font-semibold text-red-900 dark:text-red-200 mb-2">
                                Completion Not Approved
                            </p>
                            <p class="text-sm text-red-800 dark:text-red-300">
                                No points have been awarded for this completion claim.
                            </p>
                        </div>

                        <!-- Feedback Suggestion -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-5">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">
                                        Consider Providing Feedback
                                    </p>
                                    <p class="text-sm text-blue-800 dark:text-blue-300">
                                        It's helpful to let {{ participant.user_name }} know why the completion wasn't approved. Consider sending them a message in the Telegram chat with constructive feedback.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div
                    v-if="approved"
                    class="bg-gradient-to-r from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-xl p-6 text-center mb-6"
                >
                    <p class="text-sm font-semibold text-purple-900 dark:text-purple-200 mb-1">
                        Keep it up!
                    </p>
                    <p class="text-sm text-purple-800 dark:text-purple-300">
                        Your validation helps maintain the integrity of the SuperChallenge system.
                    </p>
                </div>

                <!-- Close Message -->
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    You can close this window and return to Telegram
                </p>
            </div>
        </div>
    </AppLayout>
</template>
