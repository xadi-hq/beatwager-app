<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

interface Winner {
    user_id: string;
    name: string;
    points: number;
    rank: number;
}

interface PastSeason {
    id: string;
    season_number: number;
    started_at: string;
    ended_at: string;
    duration_days: number;
    winner: Winner | null;
    total_participants: number;
    total_wagers: number;
}

interface SeasonDetails {
    id: string;
    season_number: number;
    started_at: string;
    ended_at: string;
    duration_days: number;
    final_leaderboard: Array<{
        user_id: string;
        name: string;
        points: number;
        rank: number;
    }>;
    stats: {
        total_wagers: number;
        total_participants: number;
        duration_days: number;
    };
    highlights: {
        biggest_win?: { wager_id: string; amount: number; winner_name: string };
        most_active_creator?: { user_id: string; name: string; wagers_created: number };
        most_participated_wager?: { wager_id: string; title: string; participants: number };
    };
}

const props = defineProps<{
    groupId: string;
    seasons: PastSeason[];
    currency: string;
}>();

const selectedSeasonId = ref<string | null>(null);
const seasonDetails = ref<SeasonDetails | null>(null);
const isLoading = ref(false);

// Format date for display
const formatDate = (isoString: string) => {
    const date = new Date(isoString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

// Load season details
const loadSeasonDetails = async (seasonId: string) => {
    if (selectedSeasonId.value === seasonId) {
        // Toggle off if already selected
        selectedSeasonId.value = null;
        seasonDetails.value = null;
        return;
    }

    isLoading.value = true;
    selectedSeasonId.value = seasonId;

    try {
        const response = await axios.get(`/api/groups/${props.groupId}/seasons/${seasonId}`);
        seasonDetails.value = response.data;
    } catch (error) {
        console.error('Failed to load season details:', error);
        alert('Failed to load season details. Please try again.');
        selectedSeasonId.value = null;
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="space-y-3">
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-3">
            📜 Past Seasons
        </h3>

        <!-- No Past Seasons -->
        <div v-if="seasons.length === 0" class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600 text-center">
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                No past seasons yet. Start your first season above!
            </p>
        </div>

        <!-- Past Seasons List -->
        <div v-else class="space-y-2">
            <div
                v-for="season in seasons"
                :key="season.id"
                class="border border-neutral-200 dark:border-neutral-600 rounded-lg overflow-hidden"
            >
                <!-- Season Summary -->
                <button
                    @click="loadSeasonDetails(season.id)"
                    class="w-full p-4 bg-neutral-50 dark:bg-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-600 transition-colors text-left"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-semibold text-neutral-900 dark:text-white">
                                Season {{ season.season_number }}
                            </h4>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                {{ formatDate(season.started_at) }} - {{ formatDate(season.ended_at) }}
                                <span class="mx-1">•</span>
                                {{ season.duration_days }} days
                            </p>
                        </div>
                        <div class="text-right">
                            <div v-if="season.winner" class="text-sm">
                                <span class="text-yellow-600 dark:text-yellow-400">👑</span>
                                <span class="font-semibold text-neutral-900 dark:text-white ml-1">
                                    {{ season.winner.name }}
                                </span>
                            </div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                {{ season.total_wagers }} wagers • {{ season.total_participants }} players
                            </div>
                        </div>
                    </div>
                </button>

                <!-- Season Details (Expandable) -->
                <div
                    v-if="selectedSeasonId === season.id"
                    class="border-t border-neutral-200 dark:border-neutral-600 bg-white dark:bg-neutral-800 p-4"
                >
                    <div v-if="isLoading" class="text-center py-4">
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Loading details...</p>
                    </div>

                    <div v-else-if="seasonDetails" class="space-y-4">
                        <!-- Final Leaderboard -->
                        <div>
                            <h5 class="font-semibold text-neutral-900 dark:text-white mb-2 text-sm">
                                🏆 Final Standings
                            </h5>
                            <div class="space-y-1">
                                <div
                                    v-for="player in seasonDetails.final_leaderboard.slice(0, 5)"
                                    :key="player.user_id"
                                    class="flex items-center justify-between p-2 bg-neutral-50 dark:bg-neutral-700 rounded"
                                >
                                    <div class="flex items-center gap-2">
                                        <span v-if="player.rank === 1" class="text-lg">🥇</span>
                                        <span v-else-if="player.rank === 2" class="text-lg">🥈</span>
                                        <span v-else-if="player.rank === 3" class="text-lg">🥉</span>
                                        <span v-else class="text-sm text-neutral-500 dark:text-neutral-400 w-6">{{ player.rank }}.</span>
                                        <span class="text-sm font-medium text-neutral-900 dark:text-white">
                                            {{ player.name }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-semibold text-neutral-900 dark:text-white">
                                        {{ player.points }} {{ currency }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Highlights -->
                        <div v-if="seasonDetails.highlights && Object.keys(seasonDetails.highlights).length > 0">
                            <h5 class="font-semibold text-neutral-900 dark:text-white mb-2 text-sm">
                                ✨ Season Highlights
                            </h5>
                            <div class="space-y-2">
                                <div
                                    v-if="seasonDetails.highlights.biggest_win"
                                    class="p-2 bg-green-50 dark:bg-green-900/20 rounded text-xs"
                                >
                                    <strong class="text-green-700 dark:text-green-300">💰 Biggest Win:</strong>
                                    <span class="text-neutral-700 dark:text-neutral-300 ml-1">
                                        {{ seasonDetails.highlights.biggest_win.winner_name }} won {{ seasonDetails.highlights.biggest_win.amount }} {{ currency }}
                                    </span>
                                </div>

                                <div
                                    v-if="seasonDetails.highlights.most_active_creator"
                                    class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded text-xs"
                                >
                                    <strong class="text-blue-700 dark:text-blue-300">🎯 Most Active:</strong>
                                    <span class="text-neutral-700 dark:text-neutral-300 ml-1">
                                        {{ seasonDetails.highlights.most_active_creator.name }} created {{ seasonDetails.highlights.most_active_creator.wagers_created }} wagers
                                    </span>
                                </div>

                                <div
                                    v-if="seasonDetails.highlights.most_participated_wager"
                                    class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded text-xs"
                                >
                                    <strong class="text-purple-700 dark:text-purple-300">🔥 Most Popular:</strong>
                                    <span class="text-neutral-700 dark:text-neutral-300 ml-1">
                                        "{{ seasonDetails.highlights.most_participated_wager.title }}" with {{ seasonDetails.highlights.most_participated_wager.participants }} participants
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
