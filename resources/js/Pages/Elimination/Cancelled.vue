<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    challenge: {
        id: string;
        description: string;
        cancelled_at: string;
    };
    group: {
        name: string;
    };
}>();

// Format date
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Challenge Cancelled" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <!-- Cancelled Icon -->
                <div class="text-6xl mb-4">🚫</div>

                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    Challenge Cancelled
                </h1>

                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-6">
                    This elimination challenge has been cancelled
                </p>

                <!-- Challenge Details -->
                <div class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg text-left">
                    <h2 class="font-semibold text-neutral-900 dark:text-white mb-3">
                        {{ challenge.description }}
                    </h2>

                    <div class="space-y-2 text-sm text-neutral-600 dark:text-neutral-300">
                        <div class="flex justify-between">
                            <span>Group:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ group.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Cancelled At:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ formatDate(challenge.cancelled_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Refund Notice -->
                <div class="mb-8 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="flex items-center justify-center gap-2 text-green-700 dark:text-green-300">
                        <span class="text-2xl">💰</span>
                        <div>
                            <p class="font-medium">Buy-ins Refunded</p>
                            <p class="text-sm">All participants have received their buy-ins back</p>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <a
                    href="/me"
                    class="inline-block w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors"
                >
                    Back to Dashboard
                </a>
            </div>
        </div>
    </AppLayout>
</template>
