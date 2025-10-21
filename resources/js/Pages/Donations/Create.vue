<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';

interface Recipient {
    id: string;
    name: string;
    username?: string;
    points: number;
}

const props = defineProps<{
    group: {
        id: string;
        name: string;
        currency_name: string;
    };
    donor: {
        id: string;
        name: string;
        points: number;
    };
    recipients: Recipient[];
    encrypted_user: string;
}>();

const selectedRecipient = ref<string>('');
const amount = ref<number | null>(null);
const isPublic = ref<boolean>(false);
const message = ref<string>('');
const isSubmitting = ref(false);

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Computed properties
const selectedRecipientData = computed(() => {
    return props.recipients.find(r => r.id === selectedRecipient.value);
});

const isValid = computed(() => {
    return selectedRecipient.value &&
           amount.value !== null &&
           amount.value > 0 &&
           amount.value <= props.donor.points;
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
            group_id: props.group.id,
            recipient_id: selectedRecipient.value,
            amount: amount.value,
            is_public: isPublic.value,
            message: message.value || null,
            encrypted_user: props.encrypted_user,
        });

        toastType.value = 'success';
        toastMessage.value = 'Donation sent successfully! 🎁';
        showToast.value = true;

        // Reset form
        selectedRecipient.value = '';
        amount.value = null;
        message.value = '';
        isPublic.value = false;

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
                    🎁 Send {{ group.currency_name }}
                </h1>
                <p class="text-neutral-600 dark:text-neutral-400 mb-6">
                    in <strong>{{ group.name }}</strong>
                </p>

                <!-- Donor Balance -->
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 mb-6">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        💰 Your Balance: <strong>{{ donor.points }} {{ group.currency_name }}</strong>
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Recipient Selection -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Recipient *
                        </label>
                        <select
                            v-model="selectedRecipient"
                            required
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        >
                            <option value="" disabled>Select a member</option>
                            <option v-for="recipient in recipients" :key="recipient.id" :value="recipient.id">
                                {{ recipient.name }}
                                <span v-if="recipient.username"> (@{{ recipient.username }})</span>
                                - {{ recipient.points }} {{ group.currency_name }}
                            </option>
                        </select>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Choose who receives the {{ group.currency_name }}
                        </p>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Amount *
                        </label>
                        <input
                            v-model.number="amount"
                            type="number"
                            min="1"
                            :max="donor.points"
                            required
                            :placeholder="`How many ${group.currency_name}?`"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Available: {{ donor.points }} {{ group.currency_name }}
                        </p>
                    </div>

                    <!-- Public/Private Toggle -->
                    <div class="border border-neutral-200 dark:border-neutral-600 rounded-lg p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                v-model="isPublic"
                                type="checkbox"
                                class="mt-1 rounded border-neutral-300 dark:border-neutral-600"
                            />
                            <div>
                                <span class="font-medium text-neutral-900 dark:text-white">
                                    Public Announcement
                                </span>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                    If checked: Everyone sees who sent to whom<br />
                                    If unchecked: Private - the bot announces the donation anonymously
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Optional Message -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Message (Optional)
                        </label>
                        <textarea
                            v-model="message"
                            rows="3"
                            maxlength="500"
                            :placeholder="`Add a personal message (${isPublic ? 'will be shown publicly' : 'may be incorporated into announcement'})`"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        ></textarea>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            {{ message.length }}/500 characters
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="!isValid || isSubmitting"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:from-neutral-400 disabled:to-neutral-400 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition-all"
                    >
                        {{ isSubmitting ? 'Sending...' : `🎁 Send ${amount || 0} ${group.currency_name}` }}
                    </button>

                    <!-- Info Box -->
                    <div class="p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600">
                        <p class="text-xs text-neutral-600 dark:text-neutral-400">
                            💡 <strong>How it works:</strong> {{ group.currency_name }} will be instantly transferred from your balance to the recipient.
                            A message will be posted to the group chat announcing the donation.
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
