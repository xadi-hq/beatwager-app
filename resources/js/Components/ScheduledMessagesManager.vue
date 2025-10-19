<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

interface ScheduledMessage {
    id: string;
    message_type: 'holiday' | 'birthday' | 'custom';
    title: string;
    scheduled_date: string;
    is_recurring: boolean;
    recurrence_type: 'daily' | 'weekly' | 'monthly' | 'yearly' | null;
    is_active: boolean;
    last_sent_at: string | null;
    next_occurrence: string | null;
}

const props = defineProps<{
    groupId: string;
}>();

const emit = defineEmits<{
    updated: [];
}>();

const messages = ref<ScheduledMessage[]>([]);
const isLoading = ref(false);
const showAddForm = ref(false);

// Form state
const form = ref({
    message_type: 'custom' as 'holiday' | 'birthday' | 'custom',
    title: '',
    scheduled_date: '',
    is_recurring: false,
    recurrence_type: null as 'yearly' | 'monthly' | 'weekly' | null,
});

// Load messages
const loadMessages = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/groups/${props.groupId}/messages?upcoming_only=true`);
        messages.value = response.data.messages;
    } catch (error) {
        console.error('Failed to load messages:', error);
        alert('Failed to load scheduled messages');
    } finally {
        isLoading.value = false;
    }
};

// Format date for display
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

// Get recurrence label
const getRecurrenceLabel = (msg: ScheduledMessage) => {
    if (!msg.is_recurring) return 'One-time';
    return msg.recurrence_type ? msg.recurrence_type.charAt(0).toUpperCase() + msg.recurrence_type.slice(1) : 'Recurring';
};

// Get message type emoji
const getTypeEmoji = (type: string) => {
    const emojis = { holiday: '🎉', birthday: '🎂', custom: '📅' };
    return emojis[type as keyof typeof emojis] || '📅';
};

// Add new message
const addMessage = async () => {
    if (!form.value.title || !form.value.scheduled_date) {
        alert('Please fill in all required fields');
        return;
    }

    isLoading.value = true;
    try {
        await axios.post(`/groups/${props.groupId}/messages`, form.value);

        // Reset form
        form.value = {
            message_type: 'custom',
            title: '',
            scheduled_date: '',
            is_recurring: false,
            recurrence_type: null,
        };
        showAddForm.value = false;

        // Reload messages
        await loadMessages();
        emit('updated');
    } catch (error: any) {
        console.error('Failed to add message:', error);
        alert(error.response?.data?.message || 'Failed to add scheduled message');
    } finally {
        isLoading.value = false;
    }
};

// Toggle active status
const toggleActive = async (message: ScheduledMessage) => {
    try {
        await axios.post(`/groups/${props.groupId}/messages/${message.id}/toggle`);
        message.is_active = !message.is_active;
    } catch (error) {
        console.error('Failed to toggle message:', error);
        alert('Failed to update message status');
    }
};

// Delete message
const deleteMessage = async (message: ScheduledMessage) => {
    if (!confirm(`Delete "${message.title}"?`)) return;

    try {
        await axios.delete(`/groups/${props.groupId}/messages/${message.id}`);
        await loadMessages();
        emit('updated');
    } catch (error) {
        console.error('Failed to delete message:', error);
        alert('Failed to delete message');
    }
};

// Load messages on mount
onMounted(() => {
    loadMessages();
});

// Get minimum date (today)
const minDate = computed(() => new Date().toISOString().split('T')[0]);
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                📅 Scheduled Messages
            </h3>
            <button
                @click="showAddForm = !showAddForm"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors"
            >
                {{ showAddForm ? 'Cancel' : '+ Add Message' }}
            </button>
        </div>

        <!-- Add Form -->
        <div v-if="showAddForm" class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600">
            <h4 class="font-semibold text-neutral-900 dark:text-white mb-3">Add Scheduled Message</h4>

            <div class="space-y-3">
                <!-- Message Type -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Type
                    </label>
                    <select
                        v-model="form.message_type"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white"
                    >
                        <option value="custom">Custom</option>
                        <option value="holiday">Holiday</option>
                        <option value="birthday">Birthday</option>
                    </select>
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Title *
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g., Happy New Year, John's Birthday"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white"
                    />
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Date *
                    </label>
                    <input
                        v-model="form.scheduled_date"
                        type="date"
                        :min="minDate"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white"
                    />
                </div>

                <!-- Recurring -->
                <div>
                    <label class="flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                        <input
                            v-model="form.is_recurring"
                            type="checkbox"
                            class="rounded border-neutral-300 dark:border-neutral-600"
                        />
                        Recurring message
                    </label>
                </div>

                <!-- Recurrence Type -->
                <div v-if="form.is_recurring">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Repeat
                    </label>
                    <select
                        v-model="form.recurrence_type"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white"
                    >
                        <option :value="null">Select frequency</option>
                        <option value="yearly">Yearly</option>
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button
                    @click="addMessage"
                    :disabled="isLoading"
                    class="w-full bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 disabled:bg-neutral-400 dark:disabled:bg-neutral-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
                >
                    {{ isLoading ? 'Adding...' : 'Add Message' }}
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading && !showAddForm" class="text-center py-8">
            <p class="text-neutral-500 dark:text-neutral-400">Loading messages...</p>
        </div>

        <!-- Messages List -->
        <div v-else-if="messages.length > 0" class="space-y-2">
            <div
                v-for="message in messages"
                :key="message.id"
                class="p-4 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-600 space-y-3"
            >
                <!-- Message Info -->
                <div>
                    <div class="flex items-start gap-2 mb-1">
                        <span class="text-xl">{{ getTypeEmoji(message.message_type) }}</span>
                        <h4 class="font-semibold text-neutral-900 dark:text-white">
                            {{ message.title }}
                        </h4>
                        <span
                            v-if="!message.is_active"
                            class="text-xs bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400 px-2 py-0.5 rounded"
                        >
                            Inactive
                        </span>
                    </div>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 ml-8">
                        <span>{{ formatDate(message.scheduled_date) }}</span>
                        <span class="mx-2">-</span>
                        <span>{{ getRecurrenceLabel(message) }}</span>
                        <span v-if="message.next_occurrence" class="mx-2">-</span>
                        <span v-if="message.next_occurrence">
                            Next: {{ formatDate(message.next_occurrence) }}
                        </span>
                    </div>
                </div>

                <!-- Actions Row -->
                <div class="flex items-center gap-2">
                    <button
                        @click="toggleActive(message)"
                        class="flex-1 text-sm px-4 py-2 rounded border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors font-medium"
                        :class="message.is_active ? 'text-neutral-700 dark:text-neutral-300' : 'text-green-600 dark:text-green-400'"
                    >
                        {{ message.is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button
                        @click="deleteMessage(message)"
                        class="flex-1 text-sm px-4 py-2 rounded border border-red-300 dark:border-red-600 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors font-medium"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600">
            <p class="text-neutral-600 dark:text-neutral-400 mb-2">No scheduled messages yet</p>
            <p class="text-sm text-neutral-500 dark:text-neutral-500">Click "Add Message" to schedule your first message!</p>
        </div>

        <!-- Info Box -->
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <p class="text-xs text-blue-800 dark:text-blue-200">
                <strong>💡 How It Works:</strong> Messages are sent at 8am on their scheduled date. Recurring messages repeat automatically. The bot uses your personality settings to make each message unique!
            </p>
        </div>
    </div>
</template>
