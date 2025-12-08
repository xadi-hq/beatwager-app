<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    challenge: {
        id: string;
        description: string;
        elimination_trigger: string;
    };
    participant: {
        days_survived: number;
        eliminated_at: string;
    };
    group: {
        name: string;
    };
}>();

// Format date
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
</script>

<template>
    <AppLayout>
        <Head title="Tapped Out" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <!-- Elimination Icon -->
                <div class="text-6xl mb-4">🚪</div>

                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    You've Tapped Out
                </h1>

                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-6">
                    Thanks for being honest and playing fair!
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
                            <span>Elimination Trigger:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ challenge.elimination_trigger }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stats Display -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Days Survived
                        </p>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ participant.days_survived }}
                        </p>
                    </div>
                    <div class="p-4 bg-neutral-100 dark:bg-neutral-700 rounded-lg">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Eliminated At
                        </p>
                        <p class="text-sm font-bold text-neutral-900 dark:text-white">
                            {{ formatDate(participant.eliminated_at) }}
                        </p>
                    </div>
                </div>

                <!-- Encouragement -->
                <div class="mb-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <p class="text-amber-700 dark:text-amber-300">
                        <span class="font-medium">Better luck next time!</span><br>
                        <span class="text-sm">
                            You survived {{ participant.days_survived }} {{ participant.days_survived === 1 ? 'day' : 'days' }}.
                            Keep an eye out for the next elimination challenge!
                        </span>
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <Link
                        :href="`/elimination/${challenge.id}`"
                        class="block w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors"
                    >
                        Watch the Challenge
                    </Link>
                    <a
                        href="/me"
                        class="block w-full px-6 py-3 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-lg hover:bg-neutral-300 dark:hover:bg-neutral-600 font-semibold transition-colors"
                    >
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
