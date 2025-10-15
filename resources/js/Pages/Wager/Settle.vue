<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    token: string;
    wager: {
        id: string;
        title: string;
        description?: string;
        type: string;
        deadline: string;
        stake_amount: number;
        total_points_wagered: number;
        participants_count: number;
        creator: { id: string; name: string };
        group: { id: string; name: string };
        entries: Array<{
            id: string;
            user_name: string;
            answer_value: string;
            points_wagered: number;
        }>;
        options?: string[];
    };
}>();

const form = useForm({
    token: props.token,
    outcome_value: '',
    settlement_note: '',
});

const submit = () => {
    form.post('/wager/settle');
};
</script>

<template>
    <AppLayout>
        <Head title="Settle Wager" />

        <div class="max-w-4xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-6">
                    🏁 Settle Wager
                </h1>

                <!-- Wager Details -->
                <div class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                    <h2 class="font-bold text-lg mb-2">{{ wager.title }}</h2>
                    <p v-if="wager.description" class="text-sm text-neutral-600 dark:text-neutral-300 mb-2">
                        {{ wager.description }}
                    </p>
                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                        <p>Group: {{ wager.group.name }}</p>
                        <p>Stake: {{ wager.stake_amount }} points</p>
                        <p>Total wagered: {{ wager.total_points_wagered }} points</p>
                        <p>Participants: {{ wager.participants_count }}</p>
                    </div>
                </div>

                <!-- Entries Table -->
                <div class="mb-6">
                    <h3 class="font-bold mb-3">Entries:</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-neutral-50 dark:bg-neutral-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase">User</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase">Answer</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase">Points</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                                <tr v-for="entry in wager.entries" :key="entry.id">
                                    <td class="px-4 py-2 text-sm">{{ entry.user_name }}</td>
                                    <td class="px-4 py-2 text-sm font-medium">{{ entry.answer_value }}</td>
                                    <td class="px-4 py-2 text-sm text-right">{{ entry.points_wagered }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Settlement Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Outcome Selection -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                            What was the outcome? *
                        </label>
                        
                        <!-- Binary: Yes/No -->
                        <div v-if="wager.type === 'binary'" class="space-y-2">
                            <label class="flex items-center">
                                <input
                                    v-model="form.outcome_value"
                                    type="radio"
                                    value="yes"
                                    required
                                    class="mr-2"
                                />
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    v-model="form.outcome_value"
                                    type="radio"
                                    value="no"
                                    required
                                    class="mr-2"
                                />
                                <span>No</span>
                            </label>
                        </div>

                        <!-- Multiple Choice -->
                        <div v-else-if="wager.type === 'multiple_choice' && wager.options" class="space-y-2">
                            <label v-for="option in wager.options" :key="option" class="flex items-center">
                                <input
                                    v-model="form.outcome_value"
                                    type="radio"
                                    :value="option.toLowerCase()"
                                    required
                                    class="mr-2"
                                />
                                <span>{{ option }}</span>
                            </label>
                        </div>

                        <div v-if="form.errors.outcome_value" class="text-red-600 text-sm mt-1">
                            {{ form.errors.outcome_value }}
                        </div>
                    </div>

                    <!-- Settlement Note -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                            Settlement Note (Optional)
                        </label>
                        <textarea
                            v-model="form.settlement_note"
                            rows="3"
                            placeholder="Any additional notes about the settlement..."
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Settling...' : 'Settle Wager' }}
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
