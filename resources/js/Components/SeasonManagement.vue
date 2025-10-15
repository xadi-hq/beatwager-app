<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

interface CurrentSeason {
    id: string;
    season_number: number;
    started_at: string;
    is_active: boolean;
    days_elapsed: number;
    prize_structure?: Prize[];
}

interface Prize {
    description: string;
    position: string;
}

const props = defineProps<{
    groupId: string;
    currentSeason: CurrentSeason | null;
    seasonEndsAt: string | null;
    currency: string;
}>();

const emit = defineEmits<{
    updated: [];
}>();

const isLoading = ref(false);
const showEndDateInput = ref(false);
const endDate = ref('');

// Prize management
const prizes = ref<Prize[]>([]);

const availablePositions = [
    { value: 'winner', label: 'Winner (1st place)' },
    { value: 'runner_up', label: 'Runner-up (2nd place)' },
    { value: 'loser', label: 'Loser (Last place)' },
    { value: 'most_active', label: 'Most Active (Most wagers)' },
    { value: 'most_social', label: 'Most Social (Most events attended)' },
    { value: 'most_servant', label: 'Most Servant (Most challenges completed)' },
    { value: 'most_generous', label: 'Most Generous (Most donations)' },
    { value: 'most_improved', label: 'Most Improved (Biggest point gain)' },
];

// Get positions that are still available (not selected)
const usedPositions = computed(() => prizes.value.map(p => p.position));
const availablePositionOptions = computed(() =>
    availablePositions.filter(p => !usedPositions.value.includes(p.value))
);

const canAddPrize = computed(() => availablePositionOptions.value.length > 0);

const addPrize = () => {
    if (!canAddPrize.value) return;

    prizes.value.push({
        description: '',
        position: availablePositionOptions.value[0].value
    });
};

const removePrize = (index: number) => {
    prizes.value.splice(index, 1);
};

const getPositionLabel = (value: string) => {
    return availablePositions.find(p => p.value === value)?.label || value;
};

// Format date for display
const formatDate = (isoString: string) => {
    const date = new Date(isoString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

// Calculate days remaining
const daysRemaining = computed(() => {
    if (!props.seasonEndsAt) return null;
    const end = new Date(props.seasonEndsAt);
    const now = new Date();
    const diff = Math.ceil((end.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    return diff;
});

// Start a new season
const startSeason = async () => {
    if (isLoading.value) return;

    if (!confirm('Start a new season? All member points will be reset to the starting balance.')) {
        return;
    }

    isLoading.value = true;

    try {
        const payload: { season_ends_at?: string; prize_structure?: Prize[] } = {};

        if (showEndDateInput.value && endDate.value) {
            payload.season_ends_at = endDate.value;
        }

        // Only include prizes with descriptions
        const validPrizes = prizes.value.filter(p => p.description.trim() !== '');
        if (validPrizes.length > 0) {
            payload.prize_structure = validPrizes;
        }

        await axios.post(`/groups/${props.groupId}/seasons`, payload);

        // Reload the page to get fresh data
        router.reload();
        emit('updated');
    } catch (error: any) {
        console.error('Failed to start season:', error);
        alert(error.response?.data?.message || 'Failed to start season. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

// End current season
const endSeason = async () => {
    if (isLoading.value) return;

    if (!confirm('End the current season? Final standings will be calculated and archived.')) {
        return;
    }

    isLoading.value = true;

    try {
        await axios.post(`/groups/${props.groupId}/seasons/end`);

        // Reload the page to get fresh data
        router.reload();
        emit('updated');
    } catch (error: any) {
        console.error('Failed to end season:', error);
        alert(error.response?.data?.message || 'Failed to end season. Please try again.');
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="space-y-4">
        <!-- Current Season Display -->
        <div v-if="currentSeason" class="p-4 bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                        🏆 Season {{ currentSeason.season_number }}
                    </h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Started {{ formatDate(currentSeason.started_at) }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        Day {{ Math.ceil(currentSeason.days_elapsed) || 1 }}
                    </div>
                    <div v-if="daysRemaining !== null" class="text-xs text-neutral-500 dark:text-neutral-400">
                        {{ daysRemaining > 0 ? `${daysRemaining} days left` : 'Ends today!' }}
                    </div>
                    <div v-else class="text-xs text-neutral-500 dark:text-neutral-400">
                        No end date set
                    </div>
                </div>
            </div>

            <!-- Prize Display (if prizes exist) -->
            <div v-if="currentSeason.prize_structure && currentSeason.prize_structure.length > 0" class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <h4 class="text-sm font-semibold text-yellow-900 dark:text-yellow-100 mb-2">
                    🏆 Season Prizes
                </h4>
                <ul class="space-y-1 text-sm">
                    <li
                        v-for="(prize, index) in currentSeason.prize_structure"
                        :key="index"
                        class="text-yellow-800 dark:text-yellow-200"
                    >
                        <strong>{{ getPositionLabel(prize.position) }}:</strong> {{ prize.description }}
                    </li>
                </ul>
            </div>

            <!-- End Season Button -->
            <button
                @click="endSeason"
                :disabled="isLoading"
                class="w-full mt-3 bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 disabled:bg-neutral-400 dark:disabled:bg-neutral-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
            >
                {{ isLoading ? 'Ending Season...' : 'End Season Now' }}
            </button>
        </div>

        <!-- No Season - Start New -->
        <div v-else class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                No Active Season
            </h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                Start a new season to reset all member points and begin fresh competition!
            </p>

            <!-- Optional End Date -->
            <div class="mb-4">
                <label class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300 mb-2">
                    <input
                        v-model="showEndDateInput"
                        type="checkbox"
                        class="rounded border-neutral-300 dark:border-neutral-600"
                    />
                    Set season end date (optional)
                </label>

                <div v-if="showEndDateInput" class="mt-2">
                    <input
                        v-model="endDate"
                        type="date"
                        :min="new Date().toISOString().split('T')[0]"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white"
                        placeholder="Select end date"
                    />
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        Leave blank for indefinite season (manual end only)
                    </p>
                </div>
            </div>

            <!-- Prize Configuration (Optional) -->
            <div class="mb-4">
                <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                    🏆 Prize Configuration (Optional)
                </h4>

                <!-- Existing Prizes -->
                <div v-if="prizes.length > 0" class="space-y-3 mb-3">
                    <div
                        v-for="(prize, index) in prizes"
                        :key="index"
                        class="flex gap-2 items-start"
                    >
                        <input
                            v-model="prize.description"
                            type="text"
                            placeholder="Prize description (e.g., Free dinner, Trophy)"
                            class="flex-1 px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white"
                        />
                        <select
                            v-model="prize.position"
                            class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white"
                        >
                            <!-- Keep currently selected option -->
                            <option :value="prize.position">
                                {{ getPositionLabel(prize.position) }}
                            </option>
                            <!-- Show other available options -->
                            <option
                                v-for="pos in availablePositionOptions"
                                :key="pos.value"
                                :value="pos.value"
                            >
                                {{ pos.label }}
                            </option>
                        </select>
                        <button
                            @click="removePrize(index)"
                            type="button"
                            class="px-3 py-2 text-sm bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded-lg transition-colors"
                            title="Remove prize"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- Add Prize Button -->
                <button
                    v-if="canAddPrize"
                    @click="addPrize"
                    type="button"
                    class="w-full px-4 py-2 text-sm bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg border border-green-200 dark:border-green-800 transition-colors"
                >
                    + Add Prize
                </button>

                <p v-if="prizes.length === 0" class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                    Add prizes to reward season winners! Examples: "Free dinner", "Trophy", "Wears hat of shame"
                </p>
                <p v-else-if="!canAddPrize" class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                    All prize positions assigned! Remove a prize to add a different one.
                </p>
            </div>

            <!-- Start Season Button -->
            <button
                @click="startSeason"
                :disabled="isLoading"
                class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 disabled:bg-neutral-400 dark:disabled:bg-neutral-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
            >
                {{ isLoading ? 'Starting Season...' : '🚀 Start New Season' }}
            </button>
        </div>

        <!-- Info Box -->
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <p class="text-xs text-blue-800 dark:text-blue-200">
                <strong>💡 How Seasons Work:</strong> When you start a season, all member points reset to the starting balance ({{ currency }}). When a season ends, final standings are archived and you can view the history below.
            </p>
        </div>
    </div>
</template>
