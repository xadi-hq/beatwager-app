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

interface BirthdaySuggestion {
    id: string;
    name: string;
    birthday: string | null;
    scheduled_message_id?: string;
    is_active?: boolean;
}

interface BirthdaySuggestions {
    scheduled: BirthdaySuggestion[];
    not_scheduled: BirthdaySuggestion[];
    missing_birthday: BirthdaySuggestion[];
}

const props = defineProps<{
    groupId: string;
}>();

const emit = defineEmits<{
    updated: [];
}>();

const messages = ref<ScheduledMessage[]>([]);
const birthdaySuggestions = ref<BirthdaySuggestions>({
    scheduled: [],
    not_scheduled: [],
    missing_birthday: [],
});
const isLoading = ref(false);
const showAddForm = ref(false);
const activeTab = ref<'all' | 'birthdays'>('all');

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

// Load birthday suggestions
const loadBirthdaySuggestions = async () => {
    try {
        const response = await axios.get(`/groups/${props.groupId}/messages/birthdays/suggestions`);
        birthdaySuggestions.value = response.data;
    } catch (error) {
        console.error('Failed to load birthday suggestions:', error);
    }
};

// Schedule a birthday
const scheduleBirthday = async (member: BirthdaySuggestion) => {
    if (!member.birthday) return;

    isLoading.value = true;
    try {
        await axios.post(`/groups/${props.groupId}/messages`, {
            message_type: 'birthday',
            title: member.name,
            scheduled_date: member.birthday,
            is_recurring: true,
            recurrence_type: 'yearly',
        });

        // Reload both lists
        await Promise.all([loadMessages(), loadBirthdaySuggestions()]);
        emit('updated');
    } catch (error: any) {
        console.error('Failed to schedule birthday:', error);
        alert(error.response?.data?.message || 'Failed to schedule birthday');
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
    loadBirthdaySuggestions();
});

// Get minimum date (today)
const minDate = computed(() => new Date().toISOString().split('T')[0]);
</script>

<template>
    <div class="space-y-4">
        <!-- Header with Tabs -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">
                    📅 Scheduled Messages
                </h3>
                <button
                    v-if="activeTab === 'all'"
                    @click="showAddForm = !showAddForm"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors"
                >
                    {{ showAddForm ? 'Cancel' : '+ Add Message' }}
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 border-b border-neutral-200 dark:border-neutral-600">
                <button
                    @click="activeTab = 'all'"
                    class="px-4 py-2 text-sm font-medium transition-colors"
                    :class="activeTab === 'all'
                        ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400'
                        : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
                >
                    All Messages
                </button>
                <button
                    @click="activeTab = 'birthdays'"
                    class="px-4 py-2 text-sm font-medium transition-colors"
                    :class="activeTab === 'birthdays'
                        ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400'
                        : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
                >
                    🎂 Birthdays
                    <span v-if="birthdaySuggestions.not_scheduled.length > 0" class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded">
                        {{ birthdaySuggestions.not_scheduled.length }}
                    </span>
                </button>
            </div>
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

        <!-- All Messages Tab -->
        <div v-if="activeTab === 'all'">
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

        <!-- Birthdays Tab -->
        <div v-if="activeTab === 'birthdays'" class="space-y-4">
            <!-- Scheduled Birthdays -->
            <div v-if="birthdaySuggestions.scheduled.length > 0">
                <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-2">✅ Scheduled ({{ birthdaySuggestions.scheduled.length }})</h4>
                <div class="space-y-2">
                    <div
                        v-for="member in birthdaySuggestions.scheduled"
                        :key="member.id"
                        class="p-3 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-600 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🎂</span>
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">{{ member.name }}</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ formatDate(member.birthday!) }} (Yearly)</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                v-if="!member.is_active"
                                class="text-xs bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-400 px-2 py-1 rounded"
                            >
                                Inactive
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Not Scheduled Birthdays -->
            <div v-if="birthdaySuggestions.not_scheduled.length > 0">
                <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-2">📋 Not Scheduled ({{ birthdaySuggestions.not_scheduled.length }})</h4>
                <div class="space-y-2">
                    <div
                        v-for="member in birthdaySuggestions.not_scheduled"
                        :key="member.id"
                        class="p-3 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-600 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">👤</span>
                            <div>
                                <p class="font-medium text-neutral-900 dark:text-white">{{ member.name }}</p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ formatDate(member.birthday!) }}</p>
                            </div>
                        </div>
                        <button
                            @click="scheduleBirthday(member)"
                            :disabled="isLoading"
                            class="px-3 py-1.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 disabled:bg-neutral-400 dark:disabled:bg-neutral-600 text-white rounded-lg transition-colors"
                        >
                            + Schedule
                        </button>
                    </div>
                </div>
            </div>

            <!-- Missing Birthday Info -->
            <div v-if="birthdaySuggestions.missing_birthday.length > 0">
                <h4 class="text-sm font-semibold text-neutral-900 dark:text-white mb-2">ℹ️ Missing Birthday ({{ birthdaySuggestions.missing_birthday.length }})</h4>
                <div class="space-y-2">
                    <div
                        v-for="member in birthdaySuggestions.missing_birthday"
                        :key="member.id"
                        class="p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-2xl opacity-50">👤</span>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ member.name }} <span class="italic">- No birthday set</span></p>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-3">
                    Members can add birthdays in their profile settings.
                </p>
            </div>

            <!-- Empty State -->
            <div v-if="birthdaySuggestions.scheduled.length === 0 && birthdaySuggestions.not_scheduled.length === 0 && birthdaySuggestions.missing_birthday.length === 0" class="text-center py-8 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600">
                <p class="text-neutral-600 dark:text-neutral-400 mb-2">No members in this group yet</p>
            </div>
        </div>
    </div>
</template>
