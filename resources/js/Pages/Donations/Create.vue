<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';

interface Group {
    id: string;
    name: string;
    currency_name: string;
    donor_points: number;
    platform_chat_id: string;
}

interface Recipient {
    id: string;
    name: string;
    username?: string;
    points: number;
}

const props = defineProps<{
    donor: {
        id: string;
        name: string;
    };
    groups: Group[];
}>();

const selectedGroupId = ref<string>('');
const selectedRecipient = ref<string>('');
const amount = ref<number | null>(null);
const announcementType = ref<'private' | 'public'>('private'); // Default to private DM
const message = ref<string>('');
const isSubmitting = ref(false);
const isLoadingRecipients = ref(false);
const recipients = ref<Recipient[]>([]);
const donorPoints = ref<number>(0);

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Computed properties
const selectedGroup = computed(() => {
    return props.groups.find(g => g.id === selectedGroupId.value);
});

const selectedRecipientData = computed(() => {
    return recipients.value.find(r => r.id === selectedRecipient.value);
});

const isValid = computed(() => {
    return selectedGroupId.value &&
           selectedRecipient.value &&
           amount.value !== null &&
           amount.value > 0 &&
           amount.value <= donorPoints.value;
});

// Watch for group selection changes
watch(selectedGroupId, async (newGroupId) => {
    if (!newGroupId) {
        recipients.value = [];
        selectedRecipient.value = '';
        donorPoints.value = 0;
        return;
    }

    // Update donor points
    const group = props.groups.find(g => g.id === newGroupId);
    if (group) {
        donorPoints.value = group.donor_points;
    }

    // Load recipients for this group
    isLoadingRecipients.value = true;
    try {
        const response = await axios.get(`/donations/groups/${newGroupId}/recipients`);
        recipients.value = response.data.recipients;
        donorPoints.value = response.data.donor_points;
    } catch (error: any) {
        toastType.value = 'error';
        toastMessage.value = error.response?.data?.message || 'Failed to load recipients';
        showToast.value = true;
        recipients.value = [];
    } finally {
        isLoadingRecipients.value = false;
    }
});

const submit = async () => {
    if (!isValid.value) {
        toastType.value = 'error';
        toastMessage.value = 'Please fill in all required fields with valid values';
        showToast.value = true;
        return;
    }

    isSubmitting.value = true;

    try {
        await axios.post('/donations', {
            group_id: selectedGroupId.value,
            recipient_id: selectedRecipient.value,
            amount: amount.value,
            is_public: announcementType.value === 'public',
            message: message.value || null,
        });

        toastType.value = 'success';
        toastMessage.value = 'Donation sent successfully! 🎁';
        showToast.value = true;

        // Reset form
        selectedGroupId.value = '';
        selectedRecipient.value = '';
        amount.value = null;
        message.value = '';
        announcementType.value = 'private';
        recipients.value = [];

    } catch (error: any) {
        toastType.value = 'error';
        toastMessage.value = error.response?.data?.message || 'Failed to process donation';
        showToast.value = true;
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Send Donation" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">
                    🎁 Send Points to Friends
                </h1>
                <p class="text-neutral-600 dark:text-neutral-400 mb-6">
                    Sending as <strong>{{ donor.name }}</strong>
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Group Selection -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            From Group *
                        </label>
                        <select
                            v-model="selectedGroupId"
                            required
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        >
                            <option value="" disabled>Select a group</option>
                            <option v-for="group in groups" :key="group.id" :value="group.id">
                                {{ group.name }} (your balance: {{ group.donor_points }} {{ group.currency_name }})
                            </option>
                        </select>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Choose which group's points to send
                        </p>
                    </div>

                    <!-- Recipient Selection -->
                    <div v-if="selectedGroupId">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Recipient *
                        </label>
                        <select
                            v-model="selectedRecipient"
                            required
                            :disabled="isLoadingRecipients || recipients.length === 0"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white disabled:opacity-50"
                        >
                            <option value="" disabled>
                                {{ isLoadingRecipients ? 'Loading recipients...' : 'Select a member' }}
                            </option>
                            <option v-for="recipient in recipients" :key="recipient.id" :value="recipient.id">
                                {{ recipient.name }}
                                <span v-if="recipient.username"> (@{{ recipient.username }})</span>
                                - {{ recipient.points }} {{ selectedGroup?.currency_name }}
                            </option>
                        </select>
                        <p v-if="recipients.length === 0 && !isLoadingRecipients" class="text-sm text-amber-600 dark:text-amber-400 mt-1">
                            No other members in this group
                        </p>
                    </div>

                    <!-- Amount -->
                    <div v-if="selectedGroupId">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Amount *
                        </label>
                        <input
                            v-model.number="amount"
                            type="number"
                            min="1"
                            :max="donorPoints"
                            required
                            :placeholder="`How many ${selectedGroup?.currency_name}?`"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Available balance: {{ donorPoints }} {{ selectedGroup?.currency_name }}
                        </p>
                    </div>

                    <!-- Announcement Type -->
                    <div v-if="selectedGroupId">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Announcement
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Private DM Option -->
                            <label
                                class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer transition-all"
                                :class="announcementType === 'private'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                    : 'border-neutral-200 dark:border-neutral-600 hover:border-neutral-300 dark:hover:border-neutral-500'"
                            >
                                <input
                                    type="radio"
                                    v-model="announcementType"
                                    value="private"
                                    class="sr-only"
                                />
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg">💌</span>
                                    <span class="font-medium text-neutral-900 dark:text-white">Private DM</span>
                                </div>
                                <p class="text-xs text-neutral-600 dark:text-neutral-400">
                                    Only recipient gets a personal DM
                                </p>
                            </label>

                            <!-- Public Group Option -->
                            <label
                                class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer transition-all"
                                :class="announcementType === 'public'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                    : 'border-neutral-200 dark:border-neutral-600 hover:border-neutral-300 dark:hover:border-neutral-500'"
                            >
                                <input
                                    type="radio"
                                    v-model="announcementType"
                                    value="public"
                                    class="sr-only"
                                />
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg">📢</span>
                                    <span class="font-medium text-neutral-900 dark:text-white">Public Group</span>
                                </div>
                                <p class="text-xs text-neutral-600 dark:text-neutral-400">
                                    Announced in group chat
                                </p>
                            </label>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2 italic">
                            ✨ AI crafts a personalized message for either channel
                        </p>
                    </div>

                    <!-- Optional Message -->
                    <div v-if="selectedGroupId">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Personal Message (Optional)
                        </label>
                        <textarea
                            v-model="message"
                            rows="3"
                            maxlength="500"
                            placeholder="Add a personal note to include with your gift..."
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        ></textarea>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            {{ message.length }}/500 characters
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        v-if="selectedGroupId"
                        type="submit"
                        :disabled="!isValid || isSubmitting"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:from-neutral-400 disabled:to-neutral-400 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition-all"
                    >
                        {{ isSubmitting ? 'Sending...' : `🎁 Send ${amount || 0} ${selectedGroup?.currency_name || 'points'}` }}
                    </button>

                    <!-- Info Box -->
                    <div v-if="selectedGroupId" class="p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600">
                        <p class="text-xs text-neutral-600 dark:text-neutral-400">
                            💡 <strong>How it works:</strong> Points will be instantly transferred.
                            {{ announcementType === 'private' ? 'The recipient will get a private DM.' : 'Everyone in the group will see the announcement.' }}
                        </p>
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
