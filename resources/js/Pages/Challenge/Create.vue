<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';
import FormError from '@/Components/FormError.vue';

const props = defineProps<{
    user: {
        id: string;
        name: string;
        telegram_username?: string;
    };
    defaultGroup: {
        id: string;
        name: string;
    } | null;
    groups: Array<{
        id: string;
        name: string;
        telegram_chat_title?: string;
    }>;
    groupMembers: Array<{
        name: string;
        points: number;
    }>;
}>();

const form = useForm({
    description: '',
    amount: 100,
    is_offering_service: false, // false = Type 1 (I need help), true = Type 2 (I want to help)
    group_id: props.defaultGroup?.id || '',
    completion_deadline: '',
    acceptance_deadline: '',
});

// Balance feasibility warning
const membersUnderAmount = computed(() => {
    if (!props.groupMembers || props.groupMembers.length === 0) return null;
    
    const under = props.groupMembers.filter(m => m.points < form.amount);
    if (under.length === 0) return null;
    
    const names = under.slice(0, 2).map(m => m.name).join(', ');
    const others = under.length > 2 ? ` + ${under.length - 2} other${under.length - 2 > 1 ? 's' : ''}` : '';
    
    return {
        count: under.length,
        display: names + others
    };
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Set default completion deadline to 3 days from now
const setDefaultCompletionDeadline = () => {
    const date = new Date();
    date.setDate(date.getDate() + 3);
    // Format as datetime-local string
    form.completion_deadline = date.toISOString().slice(0, 16);
};

// Set default acceptance deadline to 1 day from now
const setDefaultAcceptanceDeadline = () => {
    const date = new Date();
    date.setDate(date.getDate() + 1);
    form.acceptance_deadline = date.toISOString().slice(0, 16);
};

const clearAcceptanceDeadline = () => {
    form.acceptance_deadline = '';
};

const submit = () => {
    // Clear previous errors and toasts
    showToast.value = false;

    // Transform amount to negative if offering service (Type 2)
    form.transform((data) => ({
        ...data,
        amount: data.is_offering_service ? -Math.abs(data.amount) : Math.abs(data.amount),
        is_offering_service: undefined, // Remove UI-only field
    })).post('/challenges/store', {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Challenge created successfully!';
            showToast.value = true;

            // Will redirect to success page via backend
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to create challenge. Please check the form and try again.';
            showToast.value = true;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Create Challenge" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
                    💪 Create a New Challenge
                </h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Combined User Info and Group -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">
                            Creating as <strong>{{ user.name }}</strong>
                            <span v-if="user.telegram_username" class="text-neutral-500">
                                (@{{ user.telegram_username }})
                            </span>
                            <span v-if="defaultGroup">
                                in <strong>{{ defaultGroup.name }}</strong>
                            </span>
                        </p>
                        <p v-if="defaultGroup" class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Challenge will be created in the mentioned group above
                        </p>
                    </div>

                    <!-- Group Selection (only shown when not locked) -->
                    <div v-if="!defaultGroup">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Group *
                        </label>
                        <select
                            v-model="form.group_id"
                            required
                            :disabled="groups.length === 0"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <option value="" disabled>Select a group</option>
                            <option v-for="group in groups" :key="group.id" :value="group.id">
                                {{ group.telegram_chat_title || group.name }}
                            </option>
                        </select>
                        <p v-if="groups.length === 0" class="text-sm text-amber-600 dark:text-amber-400 mt-2">
                            ⚠️ No groups available. Make sure BeatWager bot is added to your group first, then try /newchallenge from that group.
                        </p>
                        <p v-else class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Group not listed? Make sure BeatWager bot is part of that group first.
                        </p>
                        <FormError :error="form.errors.group_id" />
                    </div>

                    <!-- Challenge Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                            What type of challenge? *
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                   :class="!form.is_offering_service
                                       ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                       : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'">
                                <input
                                    type="radio"
                                    v-model="form.is_offering_service"
                                    :value="false"
                                    class="mt-1 mr-3"
                                />
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-white">
                                        🫳 I need help (I'll pay)
                                    </div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                        You'll pay points to whoever completes the task
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                   :class="form.is_offering_service
                                       ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                       : 'border-neutral-300 dark:border-neutral-600 hover:bg-neutral-50 dark:hover:bg-neutral-700'">
                                <input
                                    type="radio"
                                    v-model="form.is_offering_service"
                                    :value="true"
                                    class="mt-1 mr-3"
                                />
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-white">
                                        🫴 I want to help (I need points)
                                    </div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                        You'll receive points from whoever accepts your offer
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Challenge Description *
                        </label>
                        <textarea
                            v-model="form.description"
                            required
                            rows="3"
                            :placeholder="form.is_offering_service
                                ? 'e.g., I\'ll clean the office kitchen this Friday for 200 points!'
                                : 'e.g., Who will clean the office kitchen by Friday? I\'ll pay 200 points for this to get done!'"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        ></textarea>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            {{ form.is_offering_service ? 'Describe what you\'ll do to earn the points' : 'Describe what needs to be done to earn the points' }}
                        </p>
                        <FormError :error="form.errors.description" />
                    </div>

                    <!-- Points Amount -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            {{ form.is_offering_service ? 'Points You\'ll Earn *' : 'Points Reward *' }}
                        </label>
                        <input
                            v-model.number="form.amount"
                            type="number"
                            required
                            min="1"
                            max="10000"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            {{ form.is_offering_service
                                ? 'Points you\'ll receive from whoever accepts this challenge'
                                : 'Points you\'ll pay to whoever completes this challenge' }}
                        </p>
                        <FormError :error="form.errors.amount" />
                    </div>

                    <!-- Deadlines -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Completion Deadline -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Must Complete By *
                            </label>
                            <div class="space-y-2">
                                <input
                                    v-model="form.completion_deadline"
                                    type="datetime-local"
                                    required
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <button
                                    type="button"
                                    @click="setDefaultCompletionDeadline"
                                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                >
                                    Set to 3 days from now
                                </button>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                When the challenge must be completed by
                            </p>
                            <FormError :error="form.errors.completion_deadline" />
                        </div>

                        <!-- Acceptance Deadline -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Accept By (optional)
                            </label>
                            <div class="space-y-2">
                                <input
                                    v-model="form.acceptance_deadline"
                                    type="datetime-local"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                                />
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="setDefaultAcceptanceDeadline"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        Set to 1 day from now
                                    </button>
                                    <button
                                        v-if="form.acceptance_deadline"
                                        type="button"
                                        @click="clearAcceptanceDeadline"
                                        class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Optional deadline for someone to accept the challenge
                            </p>
                            <FormError :error="form.errors.acceptance_deadline" />
                        </div>
                    </div>

                    <!-- Balance feasibility warning -->
                    <div v-if="membersUnderAmount" class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded text-sm">
                        <p class="text-amber-700 dark:text-amber-300">
                            💡 Note: {{ membersUnderAmount.display }} {{ membersUnderAmount.count === 1 ? 'has a' : 'have' }} balance{{ membersUnderAmount.count > 1 ? 's' : '' }} lower than your reward amount, but can still accept the challenge
                        </p>
                    </div>

                    <!-- How it works info -->
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">How it works:</h3>
                        <ul v-if="!form.is_offering_service" class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                            <li>1️⃣ One person from your group can accept this challenge</li>
                            <li>2️⃣ Your points will be held in reserve when accepted</li>
                            <li>3️⃣ They complete the task and submit proof</li>
                            <li>4️⃣ You approve or reject their submission</li>
                            <li>5️⃣ If approved, they get your points!</li>
                        </ul>
                        <ul v-else class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                            <li>1️⃣ One person from your group can accept your offer</li>
                            <li>2️⃣ Their points will be held in reserve when accepted</li>
                            <li>3️⃣ You complete the task and submit proof</li>
                            <li>4️⃣ They approve or reject your submission</li>
                            <li>5️⃣ If approved, you get their points!</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium inline-flex items-center justify-center gap-2"
                        >
                            <!-- Loading spinner -->
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Creating...' : 'Create Challenge' }}</span>
                        </button>
                    </div>
                </form>
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