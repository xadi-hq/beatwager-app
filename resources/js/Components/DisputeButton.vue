<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Drawer from '@/Components/Drawer.vue';

const props = defineProps<{
    itemType: 'wager' | 'elimination';
    itemId: string;
    canDispute: boolean;
    disputeReason?: string;
    existingDisputeId?: string | null;
    // For elimination challenges: list of survivors to select accused
    survivors?: Array<{
        id: string;
        user_id: string;
        user_name: string;
    }>;
}>();

const emit = defineEmits<{
    (e: 'disputed', disputeId: string): void;
    (e: 'error', message: string): void;
}>();

const showDrawer = ref(false);
const selectedAccusedId = ref('');

const form = useForm<{
    accused_user_id?: string;
}>({
    accused_user_id: '',
});

const actionUrl = computed(() => {
    if (props.itemType === 'wager') {
        return `/wager/${props.itemId}/dispute`;
    }
    return `/elimination/${props.itemId}/dispute`;
});

const submitDispute = () => {
    if (props.itemType === 'elimination') {
        form.accused_user_id = selectedAccusedId.value;
    }

    form.post(actionUrl.value, {
        onSuccess: (page) => {
            showDrawer.value = false;
            // The page will redirect to the dispute show page
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            emit('error', firstError ? String(firstError) : 'Failed to create dispute');
        },
    });
};

const buttonText = computed(() => {
    if (props.existingDisputeId) {
        return 'View Dispute';
    }
    return props.itemType === 'wager' ? 'Dispute Settlement' : 'Report Violation';
});

const buttonIcon = computed(() => {
    return props.existingDisputeId ? '⚖️' : '🚨';
});
</script>

<template>
    <!-- Link to existing dispute -->
    <Link
        v-if="existingDisputeId"
        :href="`/disputes/${existingDisputeId}`"
        class="flex w-full justify-center items-center gap-2 px-4 py-2 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-lg hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors"
    >
        <span>{{ buttonIcon }}</span>
        <span>{{ buttonText }}</span>
    </Link>

    <!-- Create dispute button -->
    <button
        v-else-if="canDispute"
        @click="showDrawer = true"
        class="flex w-full justify-center items-center gap-2 px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
    >
        <span>{{ buttonIcon }}</span>
        <span>{{ buttonText }}</span>
    </button>

    <!-- Disabled state with reason -->
    <div
        v-else-if="disputeReason"
        class="flex w-full justify-center items-center gap-2 px-4 py-2 bg-neutral-100 dark:bg-neutral-700 text-neutral-500 dark:text-neutral-400 rounded-lg cursor-not-allowed"
        :title="disputeReason"
    >
        <span>🚨</span>
        <span>Cannot Dispute</span>
    </div>

    <!-- Dispute Creation Drawer -->
    <Drawer
        :show="showDrawer"
        :title="itemType === 'wager' ? 'Dispute Settlement' : 'Report Violation'"
        @close="showDrawer = false"
    >
        <div class="p-4 space-y-4">
            <!-- Wager dispute explanation -->
            <div v-if="itemType === 'wager'" class="space-y-4">
                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        <strong>Warning:</strong> Filing a false dispute will result in a 10% currency penalty.
                        Only dispute if you believe the settlement outcome was incorrect.
                    </p>
                </div>

                <p class="text-neutral-600 dark:text-neutral-400">
                    By filing this dispute, you are claiming the settlement outcome was wrong.
                    Group members will vote to determine the correct outcome.
                </p>

                <button
                    @click="submitDispute"
                    :disabled="form.processing"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
                >
                    <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ form.processing ? 'Filing...' : 'File Dispute' }}</span>
                </button>
            </div>

            <!-- Elimination dispute - select accused survivor -->
            <div v-else class="space-y-4">
                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        <strong>Warning:</strong> Filing a false report will result in a 10% currency penalty.
                        Only report if you witnessed a violation.
                    </p>
                </div>

                <p class="text-neutral-600 dark:text-neutral-400">
                    Select the participant who you believe violated the elimination trigger
                    but didn't tap out. Group members will vote to confirm.
                </p>

                <!-- Survivor selection -->
                <div v-if="survivors && survivors.length > 0" class="space-y-2">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200">
                        Who violated the rules?
                    </label>
                    <div class="space-y-2">
                        <label
                            v-for="survivor in survivors"
                            :key="survivor.id"
                            class="flex items-center gap-3 p-3 border-2 border-neutral-300 dark:border-neutral-600 rounded-lg cursor-pointer transition-all hover:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20"
                        >
                            <input
                                v-model="selectedAccusedId"
                                type="radio"
                                :value="survivor.user_id"
                                class="w-5 h-5"
                            />
                            <span class="font-medium text-neutral-900 dark:text-white">{{ survivor.user_name }}</span>
                        </label>
                    </div>
                </div>
                <div v-else class="p-4 bg-neutral-100 dark:bg-neutral-700 rounded-lg">
                    <p class="text-neutral-600 dark:text-neutral-400">No active survivors to report.</p>
                </div>

                <button
                    @click="submitDispute"
                    :disabled="form.processing || !selectedAccusedId"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
                >
                    <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ form.processing ? 'Reporting...' : 'Report Violation' }}</span>
                </button>
            </div>

            <!-- Cancel button -->
            <button
                @click="showDrawer = false"
                class="w-full px-4 py-2 text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors"
            >
                Cancel
            </button>
        </div>
    </Drawer>
</template>
