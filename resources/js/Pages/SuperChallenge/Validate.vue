<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    participant: {
        id: string;
        user_name: string;
        completed_at: string;
    };
    challenge: {
        description: string;
        prize_per_person: number;
    };
    vote: 'approve' | 'reject';
}

const props = defineProps<Props>();

const form = useForm({
    confirmed: true,
});

const submit = () => {
    form.get(`/superchallenge/participant/${props.participant.id}/validate?vote=${props.vote}&confirmed=1`);
};

const isApproval = props.vote === 'approve';
</script>

<template>
    <AppLayout>
        <Head :title="isApproval ? 'Approve Completion' : 'Reject Completion'" />

        <div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
                        :class="isApproval
                            ? 'bg-gradient-to-br from-green-500 to-emerald-600'
                            : 'bg-gradient-to-br from-red-500 to-rose-600'"
                    >
                        <span class="text-3xl">{{ isApproval ? '✅' : '❌' }}</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ isApproval ? 'Approve' : 'Reject' }} Completion
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        Review {{ participant.user_name }}'s completion claim
                    </p>
                </div>

                <!-- Validation Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
                    <!-- Challenge Info -->
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                            Challenge
                        </h2>
                        <p class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                            {{ challenge.description }}
                        </p>
                    </div>

                    <!-- Participant Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <!-- Participant -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Participant</span>
                            </div>
                            <p class="text-lg font-bold text-blue-900 dark:text-blue-100">
                                {{ participant.user_name }}
                            </p>
                        </div>

                        <!-- Completed At -->
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Claimed At</span>
                            </div>
                            <p class="text-lg font-bold text-purple-900 dark:text-purple-100">
                                {{ participant.completed_at }}
                            </p>
                        </div>
                    </div>

                    <!-- Prize Info -->
                    <div
                        class="rounded-lg p-5 mb-6"
                        :class="isApproval
                            ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20'
                            : 'bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20'"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium mb-1"
                                    :class="isApproval ? 'text-gray-600 dark:text-gray-300' : 'text-red-700 dark:text-red-300'"
                                >
                                    {{ isApproval ? 'They will receive:' : 'They will NOT receive:' }}
                                </p>
                                <p
                                    class="text-3xl font-bold"
                                    :class="isApproval ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                >
                                    {{ challenge.prize_per_person }} points
                                </p>
                            </div>
                            <svg
                                class="w-12 h-12"
                                :class="isApproval ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    v-if="isApproval"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Creator Bonus Info -->
                    <div v-if="isApproval" class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-1">
                                    Your Creator Bonus
                                </p>
                                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                    You'll receive +25 points for validating this completion
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div
                        class="rounded-lg p-5"
                        :class="isApproval
                            ? 'bg-blue-50 dark:bg-blue-900/20'
                            : 'bg-orange-50 dark:bg-orange-900/20'"
                    >
                        <h3
                            class="text-sm font-semibold mb-3 flex items-center gap-2"
                            :class="isApproval
                                ? 'text-blue-900 dark:text-blue-200'
                                : 'text-orange-900 dark:text-orange-200'"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ isApproval ? 'Before you approve:' : 'Before you reject:' }}
                        </h3>
                        <ul
                            class="space-y-2 text-sm"
                            :class="isApproval
                                ? 'text-blue-800 dark:text-blue-300'
                                : 'text-orange-800 dark:text-orange-300'"
                        >
                            <li v-if="isApproval" class="flex items-start gap-2">
                                <span>•</span>
                                <span>Check the evidence they posted in the Telegram chat</span>
                            </li>
                            <li v-if="isApproval" class="flex items-start gap-2">
                                <span>•</span>
                                <span>Verify they completed the challenge as described</span>
                            </li>
                            <li v-if="!isApproval" class="flex items-start gap-2">
                                <span>•</span>
                                <span>Make sure the evidence doesn't meet the requirements</span>
                            </li>
                            <li v-if="!isApproval" class="flex items-start gap-2">
                                <span>•</span>
                                <span>Consider giving constructive feedback in the chat</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span>•</span>
                                <span>This decision is final and cannot be undone</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Action Button -->
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="w-full py-4 px-6 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    :class="isApproval
                        ? 'bg-gradient-to-r from-green-600 to-emerald-600'
                        : 'bg-gradient-to-r from-red-600 to-rose-600'"
                >
                    <span v-if="form.processing">Processing...</span>
                    <span v-else-if="isApproval">✅ Approve & Award {{ challenge.prize_per_person }} Points</span>
                    <span v-else>❌ Reject Completion</span>
                </button>

                <!-- Warning -->
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                    {{ isApproval ? 'Points will be awarded immediately' : 'This action cannot be reversed' }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
