<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    event: {
        id: string;
        name: string;
        event_date: string;
        location?: string;
        attendance_bonus: number;
    };
    group: {
        id: string;
        name: string;
    };
    user: {
        id: string;
        name: string;
    };
    groupMembers: Array<{
        id: string;
        name: string;
        telegram_username?: string;
    }>;
}>();

// Form with attendee IDs
const form = useForm({
    attendee_ids: [] as string[],
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

// Toggle attendee selection
const toggleAttendee = (userId: string) => {
    const index = form.attendee_ids.indexOf(userId);
    if (index > -1) {
        form.attendee_ids.splice(index, 1);
    } else {
        form.attendee_ids.push(userId);
    }
};

// Select all members
const selectAll = () => {
    form.attendee_ids = props.groupMembers.map(m => m.id);
};

// Deselect all
const deselectAll = () => {
    form.attendee_ids = [];
};

const submit = () => {
    // Clear previous errors and toasts
    showToast.value = false;

    form.post(`/events/${props.event.id}/attendance`, {
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Attendance recorded successfully!';
            showToast.value = true;

            // Will redirect to dashboard via backend
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to record attendance. Please try again.';
            showToast.value = true;
        },
    });
};

// Format date for display
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Record Attendance - ${event.name}`" />

        <div class="max-w-2xl mx-auto py-12 px-4">
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white mb-2">
                    ✅ Record Attendance
                </h1>
                <p class="text-neutral-600 dark:text-neutral-300 mb-6">
                    {{ event.name }}
                </p>

                <!-- Event Info -->
                <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg mb-6 space-y-2">
                    <div class="flex items-start gap-2">
                        <span class="text-neutral-600 dark:text-neutral-400 text-sm">📅</span>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">
                                {{ formatDate(event.event_date) }}
                            </p>
                        </div>
                    </div>
                    <div v-if="event.location" class="flex items-start gap-2">
                        <span class="text-neutral-600 dark:text-neutral-400 text-sm">📍</span>
                        <p class="text-sm text-neutral-700 dark:text-neutral-300">
                            {{ event.location }}
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-neutral-600 dark:text-neutral-400 text-sm">👥</span>
                        <p class="text-sm text-neutral-700 dark:text-neutral-300">
                            {{ group.name }}
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-neutral-600 dark:text-neutral-400 text-sm">🎁</span>
                        <p class="text-sm text-neutral-700 dark:text-neutral-300">
                            {{ event.attendance_bonus }} points for attendees
                        </p>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg mb-6">
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        ⚠️ <strong>First submission wins!</strong> Once submitted, attendance cannot be changed. Only select members who actually attended.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Attendee Selection -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Who attended? ({{ form.attendee_ids.length }} selected)
                            </label>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="selectAll"
                                    class="text-xs px-3 py-1 bg-neutral-200 dark:bg-neutral-600 text-neutral-900 dark:text-white rounded hover:bg-neutral-300 dark:hover:bg-neutral-500"
                                >
                                    Select All
                                </button>
                                <button
                                    type="button"
                                    @click="deselectAll"
                                    class="text-xs px-3 py-1 bg-neutral-200 dark:bg-neutral-600 text-neutral-900 dark:text-white rounded hover:bg-neutral-300 dark:hover:bg-neutral-500"
                                >
                                    Deselect All
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2 max-h-96 overflow-y-auto border border-neutral-200 dark:border-neutral-600 rounded-lg p-3">
                            <label
                                v-for="member in groupMembers"
                                :key="member.id"
                                class="flex items-center gap-3 p-3 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 cursor-pointer transition-colors"
                            >
                                <input
                                    type="checkbox"
                                    :value="member.id"
                                    :checked="form.attendee_ids.includes(member.id)"
                                    @change="toggleAttendee(member.id)"
                                    class="rounded border-neutral-300 dark:border-neutral-600 text-blue-600 focus:ring-blue-500"
                                />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">
                                        {{ member.name }}
                                    </p>
                                    <p v-if="member.telegram_username" class="text-xs text-neutral-500 dark:text-neutral-400">
                                        @{{ member.telegram_username }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        <div v-if="form.errors.attendee_ids" class="text-red-600 text-sm mt-2">
                            {{ form.errors.attendee_ids }}
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="text-sm text-green-700 dark:text-green-300">
                            <strong>{{ form.attendee_ids.length }}</strong> {{ form.attendee_ids.length === 1 ? 'person' : 'people' }} will receive
                            <strong>{{ event.attendance_bonus }} points</strong> and reset their decay timer.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing || form.attendee_ids.length === 0"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium inline-flex items-center justify-center gap-2"
                        >
                            <!-- Loading spinner -->
                            <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Recording...' : 'Record Attendance' }}</span>
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
