<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import RankingInput from '@/Components/RankingInput.vue';

const props = defineProps<{
    token: string;
    wager: {
        id: string;
        title: string;
        description?: string;
        type: string;
        type_config?: {
            max_length?: number;
            options?: string[];
            n?: number;
        };
        deadline: string;
        stake_amount: number;
        total_points_wagered: number;
        participants_count: number;
        creator: { id: string; name: string };
        group: { id: string; name: string };
        entries: Array<{
            id: string;
            user_name: string;
            answer_value: string | string[];
            points_wagered: number;
        }>;
        options?: string[];
    };
}>();

// For short_answer: array of selected entry IDs
// For top_n_ranking: actual ranking array
const form = useForm({
    token: props.token,
    outcome_value: props.wager.type === 'short_answer' ? [] : (props.wager.type === 'top_n_ranking' ? [] : ''),
    settlement_note: '',
});

// Short answer: selected entry IDs
const selectedEntryIds = ref<string[]>([]);

// Computed for checkbox state
const isEntrySelected = (entryId: string) => {
    return selectedEntryIds.value.includes(entryId);
};

const toggleEntry = (entryId: string) => {
    const index = selectedEntryIds.value.indexOf(entryId);
    if (index > -1) {
        selectedEntryIds.value.splice(index, 1);
    } else {
        selectedEntryIds.value.push(entryId);
    }
    form.outcome_value = selectedEntryIds.value;
};

// Format ranking display
const formatRanking = (ranking: string | string[]): string => {
    if (Array.isArray(ranking)) {
        return ranking.map((item, index) => `${index + 1}. ${item}`).join(', ');
    }
    try {
        const parsed = JSON.parse(ranking);
        if (Array.isArray(parsed)) {
            return parsed.map((item, index) => `${index + 1}. ${item}`).join(', ');
        }
    } catch (e) {
        // Not JSON
    }
    return ranking;
};

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

                <!-- Entries Table/List -->
                <div class="mb-6">
                    <h3 class="font-bold mb-3">Entries:</h3>

                    <!-- Short Answer: Checkbox list -->
                    <div v-if="wager.type === 'short_answer'" class="space-y-2">
                        <div
                            v-for="entry in wager.entries"
                            :key="entry.id"
                            @click="toggleEntry(entry.id)"
                            class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition-colors"
                            :class="isEntrySelected(entry.id) ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'"
                        >
                            <input
                                type="checkbox"
                                :checked="isEntrySelected(entry.id)"
                                @click.stop="toggleEntry(entry.id)"
                                class="mt-1 h-5 w-5 text-green-600 rounded focus:ring-green-500"
                            />
                            <div class="flex-1">
                                <div class="font-medium text-neutral-900 dark:text-white">{{ entry.user_name }}</div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300 mt-1">{{ entry.answer_value }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ entry.points_wagered }} points</div>
                            </div>
                        </div>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 italic">
                            Select all entries with correct/matching answers
                        </p>
                    </div>

                    <!-- Top N Ranking: Display list -->
                    <div v-else-if="wager.type === 'top_n_ranking'" class="space-y-2">
                        <div
                            v-for="entry in wager.entries"
                            :key="entry.id"
                            class="flex items-start gap-3 p-3 border border-neutral-300 dark:border-neutral-600 rounded-lg"
                        >
                            <div class="flex-1">
                                <div class="font-medium text-neutral-900 dark:text-white">{{ entry.user_name }}</div>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300 mt-1">{{ formatRanking(entry.answer_value) }}</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ entry.points_wagered }} points</div>
                            </div>
                        </div>
                    </div>

                    <!-- Standard table for other types -->
                    <div v-else class="overflow-x-auto">
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

                <!-- Debug Panel -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-700 rounded text-sm space-y-2">
                    <div><strong>Debug Info:</strong></div>
                    <div>Wager Type: <code>{{ wager.type }}</code></div>
                    <div>Type Config: <code>{{ JSON.stringify(wager.type_config) }}</code></div>
                    <div>Has Options: {{ wager.type_config?.options ? 'Yes (' + wager.type_config.options.length + ')' : 'No' }}</div>
                </div>

                <!-- Settlement Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Outcome Selection -->
                    <div>
                        <!-- Short Answer: Winners already selected via checkboxes above -->
                        <div v-if="wager.type === 'short_answer'">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">
                                Selected {{ selectedEntryIds.length }} matching {{ selectedEntryIds.length === 1 ? 'answer' : 'answers' }} as winners
                            </p>
                            <p v-if="selectedEntryIds.length === 0" class="text-sm text-amber-600 dark:text-amber-400">
                                ⚠️ No entries selected. This will refund everyone.
                            </p>
                        </div>

                        <!-- Top N Ranking: Input actual ranking -->
                        <div v-else-if="wager.type === 'top_n_ranking'">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                                What was the actual ranking? *
                            </label>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">
                                Set the correct top {{ wager.type_config?.n || 3 }} ranking. Only exact matches will win.
                            </p>
                            <!-- Debug info -->
                            <div v-if="!wager.type_config?.options || wager.type_config.options.length === 0" class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded text-sm">
                                ⚠️ Debug: No options available. type_config = {{ JSON.stringify(wager.type_config) }}
                            </div>
                            <RankingInput
                                v-model="form.outcome_value"
                                :options="wager.type_config?.options || []"
                                :n="wager.type_config?.n || 3"
                            />
                        </div>

                        <!-- Numeric: Input actual number -->
                        <div v-else-if="wager.type === 'numeric'">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                                What was the actual number? *
                            </label>
                            <input
                                v-model.number="form.outcome_value"
                                type="number"
                                required
                                placeholder="Enter the actual number"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Winner determination: {{ wager.type === 'numeric' && (wager as any).numeric_winner_type === 'exact' ? 'Exact match only' : 'Closest guess wins' }}
                            </p>
                        </div>

                        <!-- Date: Input actual date -->
                        <div v-else-if="wager.type === 'date'">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                                What was the actual date? *
                            </label>
                            <input
                                v-model="form.outcome_value"
                                type="date"
                                required
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Winner determination: {{ wager.type === 'date' && (wager as any).date_winner_type === 'exact' ? 'Exact date only' : 'Closest date wins' }}
                            </p>
                        </div>

                        <!-- Binary: Yes/No -->
                        <div v-else-if="wager.type === 'binary'" class="space-y-2">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                                What was the outcome? *
                            </label>
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
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                                What was the outcome? *
                            </label>
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

                        <!-- Debug fallback -->
                        <div v-if="wager.type !== 'short_answer' && wager.type !== 'top_n_ranking' && wager.type !== 'numeric' && wager.type !== 'date' && wager.type !== 'binary' && wager.type !== 'multiple_choice'" class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded text-sm">
                            ⚠️ Unknown wager type: {{ wager.type }}
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
