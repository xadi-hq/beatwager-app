<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    challenge: {
        id: string;
        description: string;
        elimination_trigger: string;
        elimination_mode: 'last_man_standing' | 'deadline';
        point_pot: number;
        survivor_count: number;
        eliminated_count: number;
        completion_deadline: string | null;
    };
    group: {
        name: string;
        currency: string;
    };
    participant: {
        id: string;
        days_survived: number;
    };
}>();

const form = useForm({
    elimination_note: '',
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Confirmation state
const showConfirmation = ref(false);

const openConfirmation = () => {
    showConfirmation.value = true;
};

const closeConfirmation = () => {
    showConfirmation.value = false;
};

const submit = () => {
    showToast.value = false;

    form.post(`/elimination/${props.challenge.id}/tap-out`, {
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = Object.values(errors)[0] as string || 'Failed to tap out. Please try again.';
            showToast.value = true;
            closeConfirmation();
        },
    });
};

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
</script>

<template>
    <AppLayout>
        <Head title="Tap Out - Elimination Challenge" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-3xl">🚪</span>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">
                            Tap Out
                        </h1>
                    </div>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ group.name }}
                    </p>
                </div>

                <!-- Challenge Details -->
                <div class="mb-6 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">
                        {{ challenge.description }}
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-300">
                        <span class="font-medium">Trigger:</span> {{ challenge.elimination_trigger }}
                    </p>
                </div>

                <!-- Your Progress -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Days Survived</span>
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ participant.days_survived }} {{ participant.days_survived === 1 ? 'day' : 'days' }}
                        </span>
                    </div>
                </div>

                <!-- Challenge Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-center">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Survivors</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">
                            {{ challenge.survivor_count }}
                        </p>
                    </div>
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-center">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Eliminated</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">
                            {{ challenge.eliminated_count }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg text-center">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Pot</p>
                        <p class="text-xl font-bold text-purple-600 dark:text-purple-400">
                            {{ challenge.point_pot }}
                        </p>
                    </div>
                </div>

                <!-- Elimination Note -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Elimination Note (optional)
                    </label>
                    <textarea
                        v-model="form.elimination_note"
                        rows="3"
                        placeholder="What happened? Share your story..."
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white placeholder-neutral-400"
                    ></textarea>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        This note will be visible to other participants
                    </p>
                </div>

                <!-- Warning -->
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-start gap-2">
                        <span class="text-lg">⚠️</span>
                        <div class="text-sm text-red-700 dark:text-red-300">
                            <p class="font-medium mb-1">This action is final!</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>You will be eliminated from the challenge</li>
                                <li>You will lose your buy-in</li>
                                <li>You cannot rejoin this challenge</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tap Out Button -->
                <button
                    @click="openConfirmation"
                    class="w-full px-6 py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold text-lg"
                >
                    Tap Out
                </button>

                <p class="text-center text-sm text-neutral-500 dark:text-neutral-400 mt-4">
                    Honesty is respected. Thank you for playing fair.
                </p>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div v-if="showConfirmation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-4">
                    Are you sure?
                </h3>
                <p class="text-neutral-600 dark:text-neutral-400 mb-6">
                    You survived <strong>{{ participant.days_survived }} {{ participant.days_survived === 1 ? 'day' : 'days' }}</strong>.
                    Tapping out means you'll be eliminated and lose your buy-in. This cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="closeConfirmation"
                        class="px-4 py-2 bg-neutral-300 dark:bg-neutral-600 text-neutral-700 dark:text-neutral-300 rounded-md hover:bg-neutral-400 dark:hover:bg-neutral-500"
                    >
                        Stay In
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Tapping Out...' : 'Yes, Tap Out' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <Toast
            :show="showToast"
            :type="toastType"
            :message="toastMessage"
            @close="showToast = false"
        />
    </AppLayout>
</template>
