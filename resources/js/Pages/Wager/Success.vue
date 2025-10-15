<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
    wager: {
        id: string;
        title: string;
        type: string;
        stake_amount: number;
        deadline: string;
    };
}>();
</script>

<template>
    <AppLayout>
        <Head title="Wager Created" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <div class="text-6xl mb-4">✅</div>
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    Wager Created!
                </h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-6">
                    <strong>{{ wager.title }}</strong>
                </p>
                <p class="text-neutral-600 dark:text-neutral-300 mb-8">
                    Your wager has been posted to the group. Members can now join by clicking the buttons in the Telegram chat!
                </p>
                <div class="space-y-2 text-sm text-neutral-500 dark:text-neutral-400">
                    <p>Stake: {{ wager.stake_amount }} points</p>
                    <p>Type: {{ wager.type }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
