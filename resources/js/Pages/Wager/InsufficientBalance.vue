<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    wager: {
        id: string;
        title: string;
        stake_amount: number;
        group: {
            name: string;
        };
    };
    user: {
        name: string;
        balance: number;
    };
}>();

const shortfall = props.wager.stake_amount - props.user.balance;
</script>

<template>
    <AppLayout>
        <Head title="Insufficient Balance" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <!-- Warning Icon -->
                <div class="text-6xl mb-4">💰</div>

                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    Insufficient Balance
                </h1>

                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-6">
                    You don't have enough points to join this wager
                </p>

                <!-- Balance Info -->
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">Required stake:</span>
                            <span class="font-semibold text-neutral-900 dark:text-white">{{ wager.stake_amount }} points</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">Your balance:</span>
                            <span class="font-semibold text-red-600 dark:text-red-400">{{ user.balance }} points</span>
                        </div>
                        <div class="pt-2 border-t border-red-200 dark:border-red-800 flex justify-between items-center">
                            <span class="text-neutral-600 dark:text-neutral-300">Shortfall:</span>
                            <span class="font-bold text-red-700 dark:text-red-300">{{ shortfall }} points</span>
                        </div>
                    </div>
                </div>

                <!-- Wager Details -->
                <div class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                    <h2 class="font-semibold text-neutral-900 dark:text-white mb-2">
                        {{ wager.title }}
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300">
                        in {{ wager.group.name }}
                    </p>
                </div>

                <!-- Help Text -->
                <div class="text-sm text-neutral-600 dark:text-neutral-300 space-y-2">
                    <p class="font-medium">How to earn more points:</p>
                    <ul class="text-left max-w-md mx-auto space-y-1">
                        <li>• Win wagers to earn points</li>
                        <li>• Complete challenges in the group</li>
                        <li>• Participate in group activities</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
