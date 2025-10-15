<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    wager: {
        id: string;
        title: string;
        outcome_value: string;
        settlement_note?: string;
        settled_at: string;
        group: { name: string };
    };
}>();

const settledDate = new Date(props.wager.settled_at).toLocaleString();
</script>

<template>
    <AppLayout>
        <Head title="Wager Settled" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <div class="text-6xl mb-4">🏁</div>
                
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    Wager Settled!
                </h1>

                <div class="space-y-4 text-left bg-neutral-50 dark:bg-neutral-700 rounded-lg p-6">
                    <div>
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">Question:</span>
                        <p class="text-lg text-neutral-900 dark:text-neutral-100">{{ wager.title }}</p>
                    </div>

                    <div>
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">Outcome:</span>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">
                            {{ wager.outcome_value }}
                        </p>
                    </div>

                    <div v-if="wager.settlement_note">
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">Note:</span>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">
                            {{ wager.settlement_note }}
                        </p>
                    </div>

                    <div class="text-sm text-neutral-500 dark:text-neutral-400 pt-4 border-t border-neutral-200 dark:border-neutral-600">
                        <p>Group: {{ wager.group.name }}</p>
                        <p>Settled: {{ settledDate }}</p>
                    </div>
                </div>

                <div class="mt-6 text-sm text-neutral-600 dark:text-neutral-300">
                    ✅ Points have been distributed to the winners!<br>
                    📨 The group has been notified on Telegram.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
