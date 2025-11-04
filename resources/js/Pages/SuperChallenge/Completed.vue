<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    challenge: {
        description: string;
    };
    message: string;
}

const props = defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Completion Claimed!" />

        <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-xl mx-auto">
                <div class="text-center mb-8">
                    <!-- Success Icon -->
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full mb-6 animate-pulse">
                        <span class="text-5xl">🎯</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Completion Claimed!
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">
                        Nice work! You're one step closer to your points.
                    </p>
                </div>

                <!-- Challenge Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
                    <!-- Challenge -->
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                            Challenge Completed
                        </h2>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ challenge.description }}
                        </p>
                    </div>

                    <!-- Status Message -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-5 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">
                                    Awaiting Validation
                                </p>
                                <p class="text-sm text-blue-800 dark:text-blue-300">
                                    {{ message }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Separator -->
                    <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>

                    <!-- What's Next -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            What happens next:
                        </h3>
                        <ol class="space-y-3">
                            <li class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">1</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        The challenge creator will be notified of your completion claim
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">2</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        They'll review the evidence you posted in the Telegram chat
                                    </p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-purple-600 dark:text-purple-400">3</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        If approved, your points will be awarded immediately
                                    </p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="space-y-4 mb-6">
                    <!-- Auto-approval -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-1">
                                    Auto-Approval Timer
                                </p>
                                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                    If the creator doesn't respond within 48 hours, your completion will be automatically approved.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Evidence Reminder -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-400 dark:border-purple-600 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-purple-900 dark:text-purple-200 mb-1">
                                    Don't Forget Your Evidence!
                                </p>
                                <p class="text-sm text-purple-800 dark:text-purple-300">
                                    Make sure you've posted your completion evidence (photo, screenshot, description) in the Telegram group chat.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Close Message -->
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    You can close this window and return to Telegram
                </p>
            </div>
        </div>
    </AppLayout>
</template>
