<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import StreakSettingsPanel from '@/Components/StreakSettingsPanel.vue';

const props = defineProps<{
    group: {
        id: string;
        name: string;
        description?: string;
        group_type?: string;
        points_currency_name: string;
        timezone: string;
        language?: string;
        superchallenge_frequency?: string;
        notification_preferences: {
            birthday_reminders: boolean;
            event_reminders: boolean;
            wager_reminders: boolean;
            weekly_summaries: boolean;
        };
        bot_tone?: string;
        llm_provider?: string;
        allow_nsfw?: boolean;
        has_llm_configured: boolean;
        llm_metrics?: {
            total_calls: number;
            cached_calls: number;
            fallback_calls: number;
            estimated_cost_usd: number;
            cache_hit_rate: number;
        };
    };
}>();

const emit = defineEmits<{
    updated: [];
}>();

const activeTab = ref('general');

// General settings form
const generalForm = useForm({
    points_currency_name: props.group.points_currency_name,
    timezone: props.group.timezone,
    language: props.group.language || 'en',
    description: props.group.description || '',
    superchallenge_frequency: props.group.superchallenge_frequency || 'monthly',
});

// Notification preferences form
const notificationForm = useForm({
    notification_preferences: props.group.notification_preferences,
});

// Bot personality form
const botForm = useForm({
    group_type: props.group.group_type || 'friends',
    bot_tone: props.group.bot_tone || '',
    llm_provider: props.group.llm_provider || 'openai',
    llm_api_key: '',
    allow_nsfw: props.group.allow_nsfw || false,
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

function showToastMessage(type: 'success' | 'error', message: string) {
    toastType.value = type;
    toastMessage.value = message;
    showToast.value = true;
}

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

    botForm.transform((data) => {
        // Only include API key if user provided a new one (not empty)
        if (!data.llm_api_key || data.llm_api_key.trim() === '') {
            const { llm_api_key, ...rest } = data;
            return rest;
        }
        return data;
    }).post(`/groups/${props.group.id}/settings`, {
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
                    <!-- <option value="notifications">Notifications</option> --> <!-- Hidden: Not implemented yet -->
                    <option value="streaks">Event Streaks</option>
                    <option value="bot">Bot Personality</option>
                    <option v-if="group.has_llm_configured" value="usage">LLM Usage</option>
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
                    <!-- Hidden: Not implemented yet
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
                    -->
                    <button
                        @click="activeTab = 'streaks'"
                        :class="[
                            'px-4 py-3 text-sm font-medium border-b-2',
                            activeTab === 'streaks'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                        ]"
                    >
                        Event Streaks
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
                    <button
                        v-if="group.has_llm_configured"
                        @click="activeTab = 'usage'"
                        :class="[
                            'px-4 py-3 text-sm font-medium border-b-2',
                            activeTab === 'usage'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:border-neutral-300'
                        ]"
                    >
                        LLM Usage
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
                                Timezone
                            </label>
                            <select
                                v-model="generalForm.timezone"
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                                <option value="UTC">UTC (Coordinated Universal Time)</option>
                                <option value="Europe/Amsterdam">Europe/Amsterdam (CEST/CET)</option>
                                <option value="Europe/London">Europe/London (BST/GMT)</option>
                                <option value="Europe/Paris">Europe/Paris (CEST/CET)</option>
                                <option value="Europe/Berlin">Europe/Berlin (CEST/CET)</option>
                                <option value="America/New_York">America/New York (EST/EDT)</option>
                                <option value="America/Chicago">America/Chicago (CST/CDT)</option>
                                <option value="America/Denver">America/Denver (MST/MDT)</option>
                                <option value="America/Los_Angeles">America/Los Angeles (PST/PDT)</option>
                                <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                                <option value="Asia/Shanghai">Asia/Shanghai (CST)</option>
                                <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                                <option value="Australia/Sydney">Australia/Sydney (AEDT/AEST)</option>
                            </select>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Times for wagers, events, and challenges will be displayed in this timezone
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Language
                            </label>
                            <select
                                v-model="generalForm.language"
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                                <option value="en">English</option>
                                <option value="nl">Dutch (Nederlands)</option>
                                <option value="es">Spanish (Español)</option>
                                <option value="fr">French (Français)</option>
                                <option value="de">German (Deutsch)</option>
                                <option value="it">Italian (Italiano)</option>
                                <option value="pt">Portuguese (Português)</option>
                            </select>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                The bot will respond in this language when using AI-powered messages
                            </p>
                        </div>

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
                                Customize how points are displayed in your group (e.g., "John won 100 coins")
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

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                SuperChallenge Frequency
                            </label>
                            <select
                                v-model="generalForm.superchallenge_frequency"
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                                <option value="weekly">Weekly - New challenge every 7 days</option>
                                <option value="monthly">Monthly - New challenge every month</option>
                                <option value="quarterly">Quarterly - New challenge every 3 months</option>
                            </select>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                How often the system creates collaborative SuperChallenges for your group
                            </p>
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

                <!-- Notifications Tab - Hidden: Not implemented yet -->
                <!-- <div v-if="activeTab === 'notifications'">
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
                </div> -->

                <!-- Bot Personality Tab -->
                <div v-if="activeTab === 'bot'">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Bot Personality</h3>
                    <p class="text-neutral-600 dark:text-neutral-400 mb-6">
                        Customize how your bot communicates using AI.
                    </p>

                    <form @submit.prevent="submitBot" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Group Type
                            </label>
                            <select
                                v-model="botForm.group_type"
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="friends">Friends</option>
                                <option value="colleagues">Colleagues</option>
                                <option value="family">Family</option>
                            </select>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Influences the bot's communication style and tone
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                LLM Provider
                            </label>
                            <select
                                v-model="botForm.llm_provider"
                                class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="openai">OpenAI (gpt-4o-mini)</option>
                                <option value="anthropic">Anthropic (claude-3-haiku)</option>
                                <option value="requesty">Requesty.ai (openai/gpt-4o-mini)</option>
                            </select>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Select your LLM provider and model
                            </p>
                        </div>

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

                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600">
                                <div>
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">Allow NSFW Content</div>
                                    <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                        Remove content restrictions for adult language and themes
                                    </div>
                                </div>
                                <input
                                    v-model="botForm.allow_nsfw"
                                    type="checkbox"
                                    class="w-5 h-5 text-blue-600 bg-white dark:bg-neutral-600 border-neutral-300 dark:border-neutral-500 rounded focus:ring-blue-500"
                                />
                            </label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 px-4">
                                When disabled (default), the bot keeps content PG-13. When enabled, the bot can use stronger language to match your tone.
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

                <!-- LLM Usage Tab -->
                <div v-if="activeTab === 'usage'">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">LLM Usage & Costs</h3>

                    <!-- Metrics Display -->
                    <div v-if="group.llm_metrics" class="space-y-6">
                        <!-- Overview Stats -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-neutral-50 dark:bg-neutral-700 p-4 rounded-lg border border-neutral-200 dark:border-neutral-600">
                                <div class="text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Total Calls</div>
                                <div class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                                    {{ group.llm_metrics.total_calls }}
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">API requests this month</div>
                            </div>

                            <div class="bg-neutral-50 dark:bg-neutral-700 p-4 rounded-lg border border-neutral-200 dark:border-neutral-600">
                                <div class="text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Estimated Cost</div>
                                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                    ${{ group.llm_metrics.estimated_cost_usd.toFixed(4) }}
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Total spend this month</div>
                            </div>

                            <div class="bg-neutral-50 dark:bg-neutral-700 p-4 rounded-lg border border-neutral-200 dark:border-neutral-600">
                                <div class="text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Cache Hit Rate</div>
                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                                    {{ group.llm_metrics.cache_hit_rate }}%
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Cached responses (saves cost)</div>
                            </div>

                            <div class="bg-neutral-50 dark:bg-neutral-700 p-4 rounded-lg border border-neutral-200 dark:border-neutral-600">
                                <div class="text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-1">Fallbacks</div>
                                <div class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                                    {{ group.llm_metrics.fallback_calls }}
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Used default templates</div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">💡 About These Metrics</h4>
                            <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-1">
                                <li>• Metrics are updated daily at midnight</li>
                                <li>• Cache hits save API costs by reusing previous responses</li>
                                <li>• Fallbacks occur when LLM is unavailable or errors</li>
                                <li>• Cost estimates are based on token usage and provider rates</li>
                            </ul>
                        </div>
                    </div>

                    <!-- No Data State -->
                    <div v-else class="p-8 text-center bg-neutral-50 dark:bg-neutral-700 rounded-lg border border-neutral-200 dark:border-neutral-600">
                        <div class="text-4xl mb-3">📊</div>
                        <h4 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">
                            No Usage Data Yet
                        </h4>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            Metrics will appear here after your bot starts generating AI-powered messages.
                        </p>
                    </div>
                </div>

                <!-- Event Streaks Tab -->
                <div v-if="activeTab === 'streaks'">
                    <StreakSettingsPanel
                        :group-id="group.id"
                        :points-currency-name="group.points_currency_name"
                        @saved="showToastMessage('success', 'Streak settings saved successfully!')"
                    />
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
