<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    wager: {
        id: string;
        title: string;
        betting_closes_at: string;
        status: string;
        group: {
            name: string;
        };
    };
}>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getMessage = () => {
    if (props.wager.status === 'settled') {
        return 'This wager has already been settled.';
    }
    if (props.wager.status === 'cancelled') {
        return 'This wager has been cancelled.';
    }
    return 'Betting is now closed for this wager.';
};
</script>

<template>
    <AppLayout>
        <Head title="Betting Closed" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <!-- Warning Icon -->
                <div class="text-6xl mb-4">⏰</div>

                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    Betting Closed
                </h1>

                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-6">
                    {{ getMessage() }}
                </p>

                <!-- Wager Details -->
                <div class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                    <h2 class="font-semibold text-neutral-900 dark:text-white mb-3">
                        {{ wager.title }}
                    </h2>

                    <div class="space-y-2 text-sm text-neutral-600 dark:text-neutral-300">
                        <div class="flex justify-between">
                            <span>Group:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ wager.group.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Betting Closed:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ formatDate(wager.betting_closes_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Status:</span>
                            <span class="font-medium capitalize text-neutral-900 dark:text-white">{{ wager.status }}</span>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    You can no longer join this wager. Keep an eye on the group chat for new wagers!
                </p>
            </div>
        </div>
    </AppLayout>
</template>
