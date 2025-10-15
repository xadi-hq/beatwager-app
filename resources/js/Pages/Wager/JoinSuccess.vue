<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    entry: {
        id: string;
        answer_value: string | string[];
        stake_amount: number;
        created_at: string;
    };
    wager: {
        id: string;
        title: string;
        type: 'numeric' | 'date' | 'short_answer' | 'top_n_ranking';
        currency: string;
        numeric_min?: number | null;
        numeric_max?: number | null;
        numeric_winner_type?: 'closest' | 'exact';
        date_min?: string | null;
        date_max?: string | null;
        date_winner_type?: 'closest' | 'exact';
        group: {
            name: string;
        };
    };
    user: {
        name: string;
        balance: number;
    };
}>();

// Format answer based on type
const formattedAnswer = (() => {
    if (props.wager.type === 'top_n_ranking' && Array.isArray(props.entry.answer_value)) {
        return props.entry.answer_value;
    }
    return props.entry.answer_value as string;
})();

// Format date for display
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Wager Joined Successfully" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <!-- Success Icon -->
                <div class="text-6xl mb-4">✅</div>

                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    Wager Joined!
                </h1>

                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-6">
                    You've successfully joined the wager
                </p>

                <!-- Wager Details -->
                <div class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg text-left">
                    <h2 class="font-semibold text-neutral-900 dark:text-white mb-3">
                        {{ wager.title }}
                    </h2>

                    <div class="space-y-2 text-sm text-neutral-600 dark:text-neutral-300">
                        <div class="flex justify-between">
                            <span>Group:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ wager.group.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Stake:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ entry.stake_amount }} {{ wager.currency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>New Balance:</span>
                            <span class="font-medium text-neutral-900 dark:text-white">{{ user.balance }} {{ wager.currency }}</span>
                        </div>
                    </div>
                </div>

                <!-- Answer Display -->
                <div class="mb-8 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Your Answer:
                    </p>

                    <!-- Numeric Display -->
                    <div v-if="wager.type === 'numeric'">
                        <div class="text-3xl font-bold text-neutral-900 dark:text-white mb-2">
                            {{ formattedAnswer }}
                        </div>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400">
                            <span v-if="wager.numeric_min !== null && wager.numeric_max !== null">
                                Range: {{ wager.numeric_min }} - {{ wager.numeric_max }}
                            </span>
                            <span v-else-if="wager.numeric_min !== null">
                                Min: {{ wager.numeric_min }}
                            </span>
                            <span v-else-if="wager.numeric_max !== null">
                                Max: {{ wager.numeric_max }}
                            </span>
                            • {{ wager.numeric_winner_type === 'exact' ? 'Exact match only' : 'Closest wins' }}
                        </div>
                    </div>

                    <!-- Date Display -->
                    <div v-if="wager.type === 'date'">
                        <div class="text-xl font-bold text-neutral-900 dark:text-white mb-2">
                            {{ formatDate(formattedAnswer) }}
                        </div>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400">
                            <span v-if="wager.date_min && wager.date_max">
                                Range: {{ wager.date_min }} to {{ wager.date_max }}
                            </span>
                            <span v-else-if="wager.date_min">
                                After: {{ wager.date_min }}
                            </span>
                            <span v-else-if="wager.date_max">
                                Before: {{ wager.date_max }}
                            </span>
                            • {{ wager.date_winner_type === 'exact' ? 'Exact date only' : 'Closest wins' }}
                        </div>
                    </div>

                    <!-- Short Answer Display -->
                    <div v-if="wager.type === 'short_answer'" class="text-neutral-900 dark:text-white">
                        {{ formattedAnswer }}
                    </div>

                    <!-- Ranking Display -->
                    <div v-if="wager.type === 'top_n_ranking'" class="space-y-2">
                        <div
                            v-for="(option, index) in formattedAnswer"
                            :key="index"
                            class="flex items-center gap-3 text-left"
                        >
                            <div class="flex-shrink-0 w-7 h-7 flex items-center justify-center bg-blue-600 text-white rounded-full font-bold text-sm">
                                {{ index + 1 }}
                            </div>
                            <div class="font-medium text-neutral-900 dark:text-white">
                                {{ option }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <p class="text-neutral-600 dark:text-neutral-300">
                    Your entry has been recorded and announced in the group chat. Good luck!
                </p>
            </div>
        </div>
    </AppLayout>
</template>
