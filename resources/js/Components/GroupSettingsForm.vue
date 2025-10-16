<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    group: {
        id: string;
        name: string;
        description?: string;
        points_currency_name: string;
        notification_preferences: {
            birthday_reminders: boolean;
            event_reminders: boolean;
            wager_reminders: boolean;
            weekly_summaries: boolean;
        };
        bot_tone?: string;
        has_llm_configured: boolean;
    };
}>();

const emit = defineEmits<{
    updated: [];
}>();

const activeTab = ref('general');

// General settings form
const generalForm = useForm({
    points_currency_name: props.group.points_currency_name,
    description: props.group.description || '',
});

// Notification preferences form
const notificationForm = useForm({
    notification_preferences: props.group.notification_preferences,
});

// Bot personality form
const botForm = useForm({
    bot_tone: props.group.bot_tone || '',
    llm_api_key: '',
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

function submitGeneral() {
    showToast.value = false;

    generalForm.post(`/groups/${props.group.id}/settings`, {
        preserveScroll: true,
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'General settings updated successfully!';
            showToast.value = true;
            emit('updated');
        },
        onError: () => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to update general settings.';
            showToast.value = true;
        },
    });
}

function submitNotifications() {
    showToast.value = false;

    notificationForm.post(`/groups/${props.group.id}/settings`, {
        preserveScroll: true,
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Notification preferences updated successfully!';
            showToast.value = true;
            emit('updated');
        },
        onError: () => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to update notification preferences.';
            showToast.value = true;
        },
    });
}

function submitBot() {
    showToast.value = false;

    botForm.post(`/groups/${props.group.id}/settings`, {
        preserveScroll: true,
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Bot personality settings updated successfully!';
            showToast.value = true;
            // Clear the API key field after successful submission
            botForm.llm_api_key = '';
            emit('updated');
        },
        onError: () => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to update bot personality settings.';
            showToast.value = true;
        },
    });
}
</script>

<template>
    <div>
        <!-- Tabs -->
        <div class="bg-white dark:bg-neutral-800 rounded-lg">
            <!-- Mobile: Dropdown -->
            <div class="sm:hidden border-b border-neutral-200 dark:border-neutral-700 mb-4">
                <select
                    v-model="activeTab"
                    class="w-full px-4 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="general">General</option>
                    <option value="notifications">Notifications</option>
                    <option value="bot">Bot Personality</option>
                </select>
            </div>

            <!-- Desktop: Horizontal tabs -->
            <div class="hidden sm:block border-b border-neutral-200 dark:border-neutral-700">
                <nav class="flex -mb-px">
                    <button
                        @click="activeTab = 'general'"
                        :class="[
                            'px-4 py-3 text-sm font-medium border-b-2',
                            activeTab === 'general'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                        ]"
                    >
                        General
                    </button>
                    <button
                        @click="activeTab = 'notifications'"
                        :class="[
                            'px-4 py-3 text-sm font-medium border-b-2',
                            activeTab === 'notifications'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                        ]"
                    >
                        Notifications
                    </button>
                    <button
                        @click="activeTab = 'bot'"
                        :class="[
                            'px-4 py-3 text-sm font-medium border-b-2',
                            activeTab === 'bot'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                        ]"
                    >
                        Bot Personality
                    </button>
                </nav>
            </div>

            <div class="py-4">
                <!-- General Tab -->
                <div v-if="activeTab === 'general'">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">General Settings</h3>

                    <form @submit.prevent="submitGeneral" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Points Currency Name
                            </label>
                            <input
                                v-model="generalForm.points_currency_name"
                                type="text"
                                maxlength="50"
                                placeholder="e.g., points, kudos, eth, coins"
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            />
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Customize how points are displayed in your group (e.g., "John won 100 kudos")
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Group Description (Optional)
                            </label>
                            <textarea
                                v-model="generalForm.description"
                                rows="3"
                                maxlength="500"
                                placeholder="A brief description of your group..."
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            :disabled="generalForm.processing"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ generalForm.processing ? 'Saving...' : 'Save General Settings' }}
                        </button>
                    </form>
                </div>

                <!-- Notifications Tab -->
                <div v-if="activeTab === 'notifications'">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Notification Preferences</h3>

                    <form @submit.prevent="submitNotifications" class="space-y-6">
                        <div class="space-y-4">
                            <label class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">Birthday Reminders</div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400">Send reminders for group members' birthdays</div>
                                </div>
                                <input
                                    v-model="notificationForm.notification_preferences.birthday_reminders"
                                    type="checkbox"
                                    class="w-5 h-5 text-blue-600 bg-white dark:bg-neutral-600 border-neutral-300 dark:border-neutral-500 rounded focus:ring-blue-500"
                                />
                            </label>

                            <label class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">Event Reminders</div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400">Notify about upcoming events</div>
                                </div>
                                <input
                                    v-model="notificationForm.notification_preferences.event_reminders"
                                    type="checkbox"
                                    class="w-5 h-5 text-blue-600 bg-white dark:bg-neutral-600 border-neutral-300 dark:border-neutral-500 rounded focus:ring-blue-500"
                                />
                            </label>

                            <label class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">Wager Reminders</div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400">Send reminders for wager deadlines</div>
                                </div>
                                <input
                                    v-model="notificationForm.notification_preferences.wager_reminders"
                                    type="checkbox"
                                    class="w-5 h-5 text-blue-600 bg-white dark:bg-neutral-600 border-neutral-300 dark:border-neutral-500 rounded focus:ring-blue-500"
                                />
                            </label>

                            <label class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">Weekly Summaries</div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400">Receive weekly activity summaries</div>
                                </div>
                                <input
                                    v-model="notificationForm.notification_preferences.weekly_summaries"
                                    type="checkbox"
                                    class="w-5 h-5 text-blue-600 bg-white dark:bg-neutral-600 border-neutral-300 dark:border-neutral-500 rounded focus:ring-blue-500"
                                />
                            </label>
                        </div>

                        <button
                            type="submit"
                            :disabled="notificationForm.processing"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ notificationForm.processing ? 'Saving...' : 'Save Notification Preferences' }}
                        </button>
                    </form>
                </div>

                <!-- Bot Personality Tab -->
                <div v-if="activeTab === 'bot'">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Bot Personality</h3>
                    <p class="text-neutral-600 dark:text-neutral-400 mb-6">
                        Customize how your bot communicates using AI.
                    </p>

                    <form @submit.prevent="submitBot" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                LLM API Key (Optional)
                            </label>
                            <input
                                v-model="botForm.llm_api_key"
                                type="password"
                                maxlength="500"
                                placeholder="sk-..."
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                <span v-if="group.has_llm_configured" class="text-green-600 dark:text-green-400">✓ API key configured</span>
                                <span v-else>OpenAI-compatible API key (OpenRouter, OpenAI, etc.)</span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Bot Tone & Personality (Optional)
                            </label>
                            <textarea
                                v-model="botForm.bot_tone"
                                rows="4"
                                maxlength="1000"
                                placeholder="e.g., 'sarcastic and witty', 'professional and formal', 'encouraging and supportive'"
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            ></textarea>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Describe how you want the bot to communicate. The LLM will adapt messages to match this tone.
                            </p>
                        </div>

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Examples:</strong><br>
                                • "Sarcastic and playful, like a competitive friend"<br>
                                • "Professional and concise, like a sports commentator"<br>
                                • "Encouraging and supportive, celebrates wins enthusiastically"
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="botForm.processing"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ botForm.processing ? 'Saving...' : 'Save Bot Settings' }}
                        </button>
                    </form>
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
    </div>
</template>
