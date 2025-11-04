<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    group: {
        id: string;
        name: string;
        platform_chat_title: string | null;
    };
}

const props = defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Nudge Declined" />

        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md mx-auto">
                <div class="text-center">
                    <!-- Icon -->
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-full mb-6">
                        <span class="text-4xl">👋</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        No Problem!
                    </h1>

                    <!-- Message -->
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
                        You've declined the SuperChallenge nudge for <span class="font-semibold">{{ group.name }}</span>. Maybe next time!
                    </p>

                    <!-- Info Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
                        <div class="flex items-start gap-3 text-left">
                            <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                    What happens now?
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    The system will randomly select another member from your group to create a SuperChallenge. You can still participate when it's created!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Close message -->
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        You can close this window and return to Telegram
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
