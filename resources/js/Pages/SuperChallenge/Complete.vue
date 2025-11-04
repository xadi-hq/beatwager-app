<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    challenge: {
        id: string;
        description: string;
        evidence_guidance: string | null;
    };
}

const props = defineProps<Props>();

const form = useForm({});

const submit = () => {
    form.post(`/superchallenge/${props.challenge.id}/complete`);
};
</script>

<template>
    <AppLayout>
        <Head title="Claim Completion" />

        <div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full mb-4">
                        <span class="text-3xl">✅</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Claim Completion
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        You're about to claim you've completed this challenge
                    </p>
                </div>

                <!-- Challenge Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
                    <!-- Challenge Description -->
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                            The Challenge
                        </h2>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ challenge.description }}
                        </p>
                    </div>

                    <!-- Evidence Reminder -->
                    <div v-if="challenge.evidence_guidance" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-5 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">
                                    Required Evidence:
                                </p>
                                <p class="text-sm text-blue-800 dark:text-blue-300">
                                    {{ challenge.evidence_guidance }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-5">
                        <h3 class="text-sm font-semibold text-purple-900 dark:text-purple-200 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            What happens next?
                        </h3>
                        <ol class="space-y-2 text-sm text-purple-800 dark:text-purple-300">
                            <li class="flex items-start gap-2">
                                <span class="font-bold">1.</span>
                                <span>You'll be redirected back to Telegram</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold">2.</span>
                                <span>Post your evidence in the group chat (photo, screenshot, description, etc.)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold">3.</span>
                                <span>The challenge creator will review and validate your completion</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold">4.</span>
                                <span>Once approved, you'll receive your points!</span>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Important Note -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-200 mb-1">
                                Make sure you're ready!
                            </p>
                            <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                Only claim completion if you've actually finished the challenge and have evidence ready to share in the chat.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="w-full py-4 px-6 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    {{ form.processing ? 'Claiming...' : 'Claim Completion' }}
                </button>

                <!-- Help Text -->
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                    Be honest - the creator will validate your completion
                </p>
            </div>
        </div>
    </AppLayout>
</template>
