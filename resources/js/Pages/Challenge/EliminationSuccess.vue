<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
    challenge: {
        id: string;
        description: string;
        elimination_trigger: string;
        elimination_mode: 'last_man_standing' | 'deadline';
        point_pot: number;
        buy_in_amount: number;
        completion_deadline?: string;
        tap_in_deadline?: string;
    };
    group: {
        name: string;
    };
}>();
</script>

<template>
    <AppLayout>
        <Head title="Elimination Challenge Created" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-8 text-center">
                <div class="text-6xl mb-4">🎯</div>
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-white mb-4">
                    Elimination Challenge Created!
                </h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-300 mb-2">
                    <strong>{{ challenge.description }}</strong>
                </p>
                <p class="text-neutral-500 dark:text-neutral-400 mb-6">
                    in <strong>{{ group.name }}</strong>
                </p>

                <div class="space-y-3 text-sm text-neutral-600 dark:text-neutral-300 mb-8 text-left max-w-md mx-auto">
                    <div class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <span class="text-xl">🚫</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Elimination Trigger</div>
                            <div class="text-neutral-600 dark:text-neutral-400">{{ challenge.elimination_trigger }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <span class="text-xl">{{ challenge.elimination_mode === 'last_man_standing' ? '🏆' : '⏰' }}</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">
                                {{ challenge.elimination_mode === 'last_man_standing' ? 'Last One Standing' : 'Deadline Mode' }}
                            </div>
                            <div class="text-neutral-600 dark:text-neutral-400">
                                {{ challenge.elimination_mode === 'last_man_standing'
                                    ? 'Continues until only one survivor remains'
                                    : 'Survivors at deadline split the pot' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <span class="text-xl">💰</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Prize Pool</div>
                            <div class="text-neutral-600 dark:text-neutral-400">
                                {{ challenge.point_pot.toLocaleString() }} points
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <span class="text-xl">🎟️</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Buy-In</div>
                            <div class="text-neutral-600 dark:text-neutral-400">
                                {{ challenge.buy_in_amount.toLocaleString() }} points per participant
                            </div>
                        </div>
                    </div>

                    <div v-if="challenge.completion_deadline" class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <span class="text-xl">📅</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Ends</div>
                            <div class="text-neutral-600 dark:text-neutral-400">
                                {{ new Date(challenge.completion_deadline).toLocaleString() }}
                            </div>
                        </div>
                    </div>

                    <div v-if="challenge.tap_in_deadline" class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <span class="text-xl">⏳</span>
                        <div>
                            <div class="font-medium text-neutral-900 dark:text-white">Tap-In By</div>
                            <div class="text-neutral-600 dark:text-neutral-400">
                                {{ new Date(challenge.tap_in_deadline).toLocaleString() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg text-left">
                    <h3 class="font-semibold text-orange-900 dark:text-orange-100 mb-2">What happens next?</h3>
                    <ul class="text-sm text-orange-800 dark:text-orange-200 space-y-1">
                        <li>1. Group members will see a "Tap In" button in Telegram</li>
                        <li>2. Participants pay the buy-in to join the challenge</li>
                        <li>3. When someone triggers the condition, they "Tap Out"</li>
                        <li v-if="challenge.elimination_mode === 'last_man_standing'">
                            4. Last survivor takes the entire pot!
                        </li>
                        <li v-else>
                            4. All survivors at the deadline split the pot equally
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
