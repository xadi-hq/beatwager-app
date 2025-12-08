<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    challenge: {
        id: string;
        description: string;
        elimination_trigger: string;
        elimination_mode: 'last_man_standing' | 'deadline';
        point_pot: number;
        buy_in_amount: number;
        completion_deadline: string | null;
        participant_count: number;
    };
    group: {
        name: string;
        currency: string;
    };
}>();

// Calculate potential winnings per survivor
const potentialWinnings = computed(() => {
    return Math.floor(props.challenge.point_pot / props.challenge.participant_count);
});

// Format date
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'No deadline';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Elimination mode display
const eliminationModeText = computed(() => {
    return props.challenge.elimination_mode === 'last_man_standing'
        ? 'Last Man Standing'
        : 'Deadline Mode';
});
</script>

<template>
    <AppLayout>
        <Head title="Tapped In Successfully" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <!-- Success Icon -->
                <div class="text-6xl mb-4">🎯</div>

                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    You're In!
                </h1>

                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-6">
                    You've successfully tapped in to the elimination challenge
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
                            <span>Mode:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ eliminationModeText }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Elimination Trigger:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ challenge.elimination_trigger }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Buy-in Paid:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ challenge.buy_in_amount }} {{ group.currency }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stats Display -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Total Pot
                        </p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ challenge.point_pot }} {{ group.currency }}
                        </p>
                    </div>
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <p class="text-sm font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Participants
                        </p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ challenge.participant_count }}
                        </p>
                    </div>
                </div>

                <!-- Potential Winnings -->
                <div class="mb-8 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                    <p class="text-sm text-neutral-600 dark:text-neutral-300 mb-1">
                        Potential Winnings (if you survive)
                    </p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                        ~{{ potentialWinnings }} {{ group.currency }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        Per survivor (split equally)
                    </p>
                </div>

                <!-- What's Next -->
                <div class="mb-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-left">
                    <h3 class="font-semibold text-amber-900 dark:text-amber-100 mb-2">
                        What happens next?
                    </h3>
                    <ol class="text-sm text-amber-700 dark:text-amber-300 space-y-2 list-decimal list-inside">
                        <li>Stay alert! Avoid the elimination trigger: <strong>{{ challenge.elimination_trigger }}</strong></li>
                        <li>If you slip up, tap out honestly to maintain your reputation</li>
                        <li>Survivors split the pot when the challenge ends</li>
                        <li v-if="challenge.completion_deadline">Challenge ends: {{ formatDate(challenge.completion_deadline) }}</li>
                    </ol>
                </div>

                <!-- Action Button -->
                <Link
                    :href="`/elimination/${challenge.id}`"
                    class="inline-block w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors"
                >
                    View Challenge Details
                </Link>

                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-4">
                    Good luck! Stay strong! 💪
                </p>
            </div>
        </div>
    </AppLayout>
</template>
