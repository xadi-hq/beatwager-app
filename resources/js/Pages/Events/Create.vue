<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';

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
}>();

const form = useForm({
    name: '',
    description: '',
    event_date: '',
    location: '',
    group_id: props.defaultGroup?.id || '',
    attendance_bonus: 100,
    rsvp_enabled: true,
    rsvp_deadline: '',
    auto_prompt_hours_after: 2,
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Computed helper for RSVP deadline minimum (must be before event date)
const maxRsvpDeadline = computed(() => {
    if (!form.event_date) return '';
    return form.event_date;
});

const submit = () => {
    // Clear previous errors and toasts
    showToast.value = false;

    form.post('/events/store', {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Event created successfully!';
            showToast.value = true;

            // Will redirect to dashboard via backend
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to create event. Please check the form and try again.';
            showToast.value = true;
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Create Event" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-6">
                    📅 Create a New Event
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
                            Event will be created in the group where you used /newevent
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
                            ⚠️ No groups available. Make sure BeatWager bot is added to your group first, then try /newevent from that group.
                        </p>
                        <p v-else class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Group not listed? Make sure BeatWager bot is part of that group first.
                        </p>
                        <div v-if="form.errors.group_id" class="text-red-600 text-sm mt-1">{{ form.errors.group_id }}</div>
                    </div>

                    <!-- Event Name -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Event Name *
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="e.g., Group Meetup at Central Park"
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        />
                        <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                            Description
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Optional details about the event..."
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                        ></textarea>
                        <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
                    </div>

                    <!-- Event Date and Location -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Event Date -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Event Date & Time *
                            </label>
                            <input
                                v-model="form.event_date"
                                type="datetime-local"
                                required
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <div v-if="form.errors.event_date" class="text-red-600 text-sm mt-1">{{ form.errors.event_date }}</div>
                        </div>

                        <!-- Location -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Location
                            </label>
                            <input
                                v-model="form.location"
                                type="text"
                                placeholder="e.g., Central Park"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <div v-if="form.errors.location" class="text-red-600 text-sm mt-1">{{ form.errors.location }}</div>
                        </div>
                    </div>

                    <!-- Attendance Settings -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg space-y-4">
                        <h3 class="font-medium text-neutral-900 dark:text-white">Attendance Settings</h3>

                        <!-- Attendance Bonus -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Attendance Bonus (points) *
                            </label>
                            <input
                                v-model.number="form.attendance_bonus"
                                type="number"
                                required
                                min="0"
                                max="1000"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Points awarded to attendees (also resets decay timer)
                            </p>
                            <div v-if="form.errors.attendance_bonus" class="text-red-600 text-sm mt-1">{{ form.errors.attendance_bonus }}</div>
                        </div>

                        <!-- Auto-Prompt Hours -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Attendance Reminder (hours after event) *
                            </label>
                            <input
                                v-model.number="form.auto_prompt_hours_after"
                                type="number"
                                required
                                min="0"
                                max="24"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Bot will prompt for attendance recording after this delay
                            </p>
                            <div v-if="form.errors.auto_prompt_hours_after" class="text-red-600 text-sm mt-1">{{ form.errors.auto_prompt_hours_after }}</div>
                        </div>
                    </div>

                    <!-- RSVP Settings -->
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg space-y-4">
                        <div class="flex items-center gap-2">
                            <input
                                v-model="form.rsvp_enabled"
                                type="checkbox"
                                id="rsvp_enabled"
                                class="rounded border-neutral-300 dark:border-neutral-600"
                            />
                            <label for="rsvp_enabled" class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Enable RSVP
                            </label>
                        </div>

                        <!-- RSVP Deadline (only show if RSVP enabled) -->
                        <div v-if="form.rsvp_enabled">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                RSVP Deadline (optional)
                            </label>
                            <input
                                v-model="form.rsvp_deadline"
                                type="datetime-local"
                                :max="maxRsvpDeadline"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                Leave blank for no deadline
                            </p>
                            <div v-if="form.errors.rsvp_deadline" class="text-red-600 text-sm mt-1">{{ form.errors.rsvp_deadline }}</div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium inline-flex items-center justify-center gap-2"
                        >
                            <!-- Loading spinner -->
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Creating...' : 'Create Event' }}</span>
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
