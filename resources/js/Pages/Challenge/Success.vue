<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
    challenge: {
        id: string;
        description: string;
        amount: number;
        completion_deadline: string;
        acceptance_deadline?: string;
    };
}>();
</script>

<template>
    <AppLayout>
        <Head title="Challenge Created" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <div class="text-6xl mb-4">💪</div>
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    Challenge Created!
                </h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-6">
                    <strong>{{ challenge.description }}</strong>
                </p>
                <p class="text-neutral-600 dark:text-neutral-300 mb-8">
                    Your challenge has been posted to the group. The first person to click "Accept" will take on the challenge!
                </p>
                <div class="space-y-2 text-sm text-neutral-500 dark:text-neutral-400">
                    <p>💰 Reward: {{ challenge.amount }} points</p>
                    <p>⏱️ Complete by: {{ new Date(challenge.completion_deadline).toLocaleString() }}</p>
                    <p v-if="challenge.acceptance_deadline">
                        📅 Accept by: {{ new Date(challenge.acceptance_deadline).toLocaleString() }}
                    </p>
                </div>
                
                <div class="mt-8 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                    <h3 class="font-semibold text-purple-900 dark:text-purple-100 mb-2">What happens next?</h3>
                    <ul class="text-sm text-purple-800 dark:text-purple-200 space-y-1">
                        <li>🔔 Group members will see an "Accept" button in Telegram</li>
                        <li>🏃 First person to accept gets the challenge</li>
                        <li>🔒 Your points are reserved until completion</li>
                        <li>✅ You'll get a notification when they submit their work</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>