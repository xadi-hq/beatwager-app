<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    event: {
        id: string;
        name: string;
        description?: string;
        event_date: string;
        location?: string;
        status: 'upcoming' | 'completed' | 'expired' | 'cancelled';
        attendance_bonus: number;
        rsvp_enabled: boolean;
        rsvp_deadline?: string;
    };
    group: {
        id: string;
        name: string;
    };
    rsvps: {
        going: number;
        maybe: number;
        not_going: number;
    };
    attendance?: {
        total_attendees: number;
        attendees: Array<{
            name: string;
            telegram_username?: string;
        }>;
    };
    groupMembers: Array<{
        id: string;
        name: string;
        telegram_username?: string;
        rsvp_status: 'going' | 'maybe' | 'not_going' | 'pending';
        attended: boolean | null;
    }>;
}>();

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

// Status badge styling
const statusColors = {
    upcoming: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
    completed: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
    expired: 'bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300',
    cancelled: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
};

const statusEmoji = {
    upcoming: '📅',
    completed: '✅',
    expired: '⏰',
    cancelled: '❌',
};

// RSVP status styling
const rsvpColors = {
    going: 'text-green-600 dark:text-green-400',
    maybe: 'text-yellow-600 dark:text-yellow-400',
    not_going: 'text-red-600 dark:text-red-400',
    pending: 'text-neutral-400 dark:text-neutral-500',
};

const rsvpEmoji = {
    going: '✅',
    maybe: '🤔',
    not_going: '❌',
    pending: '⏳',
};
</script>

<template>
    <AppLayout>
        <Head :title="event.name" />

        <div class="max-w-7xl mx-auto py-12 px-4">
            <!-- Mobile/Tablet: Single column layout -->
            <div class="lg:hidden">
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6 mb-6">
                    <!-- Header -->
                    <div class="mb-6">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">
                                {{ event.name }}
                            </h1>
                            <span
                                :class="statusColors[event.status]"
                                class="px-3 py-1 rounded-full text-sm font-medium inline-flex items-center gap-1 flex-shrink-0"
                            >
                                {{ statusEmoji[event.status] }}
                                {{ event.status.charAt(0).toUpperCase() + event.status.slice(1) }}
                            </span>
                        </div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ group.name }}</p>
                        <p v-if="event.description" class="text-neutral-600 dark:text-neutral-300 mt-2">
                            {{ event.description }}
                        </p>
                    </div>

                    <!-- Event Details -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <span class="text-2xl">📅</span>
                            <div>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Date & Time</p>
                                <p class="text-neutral-900 dark:text-white font-medium">
                                    {{ formatDate(event.event_date) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="event.location" class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <span class="text-2xl">📍</span>
                            <div>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Location</p>
                                <p class="text-neutral-900 dark:text-white font-medium">{{ event.location }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <span class="text-2xl">💰</span>
                            <div>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Attendance Bonus</p>
                                <p class="text-neutral-900 dark:text-white font-medium">{{ event.attendance_bonus }} {{ event.currency || 'points' }}</p>
                            </div>
                        </div>

                        <div v-if="event.rsvp_enabled" class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <span class="text-2xl">🎟️</span>
                            <div class="flex-1">
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">RSVPs</p>
                                <div class="flex gap-4 text-sm">
                                    <span class="text-green-600 dark:text-green-400">✅ {{ rsvps.going }}</span>
                                    <span class="text-yellow-600 dark:text-yellow-400">🤔 {{ rsvps.maybe }}</span>
                                    <span class="text-red-600 dark:text-red-400">❌ {{ rsvps.not_going }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Dashboard Button -->
                    <div class="mt-6">
                        <a href="/me" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Attendees -->
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">Attendees</h2>
                    <div class="space-y-2">
                        <div
                            v-for="member in groupMembers"
                            :key="member.id"
                            class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700 rounded"
                        >
                            <div>
                                <p class="text-neutral-900 dark:text-white font-medium">{{ member.name }}</p>
                                <p v-if="member.telegram_username" class="text-sm text-neutral-500 dark:text-neutral-400">@{{ member.telegram_username }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span v-if="member.attended !== null" :class="member.attended ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    {{ member.attended ? '✅ Attended' : '❌ Absent' }}
                                </span>
                                <span v-else :class="rsvpColors[member.rsvp_status]">
                                    {{ rsvpEmoji[member.rsvp_status] }}
                                    {{ member.rsvp_status === 'pending' ? 'Pending' : member.rsvp_status.charAt(0).toUpperCase() + member.rsvp_status.slice(1).replace('_', ' ') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop: Two column layout -->
            <div class="hidden lg:grid lg:grid-cols-2 lg:gap-6">
                <!-- Left column: Event details -->
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                    <div class="mb-6">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">
                                {{ event.name }}
                            </h1>
                            <span
                                :class="statusColors[event.status]"
                                class="px-3 py-1 rounded-full text-sm font-medium inline-flex items-center gap-1 flex-shrink-0"
                            >
                                {{ statusEmoji[event.status] }}
                                {{ event.status.charAt(0).toUpperCase() + event.status.slice(1) }}
                            </span>
                        </div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ group.name }}</p>
                        <p v-if="event.description" class="text-neutral-600 dark:text-neutral-300 mt-2">
                            {{ event.description }}
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <span class="text-2xl">📅</span>
                            <div>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Date & Time</p>
                                <p class="text-neutral-900 dark:text-white font-medium">
                                    {{ formatDate(event.event_date) }}
                                </p>
                            </div>
                        </div>

                        <div v-if="event.location" class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <span class="text-2xl">📍</span>
                            <div>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Location</p>
                                <p class="text-neutral-900 dark:text-white font-medium">{{ event.location }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <span class="text-2xl">💰</span>
                            <div>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Attendance Bonus</p>
                                <p class="text-neutral-900 dark:text-white font-medium">{{ event.attendance_bonus }} {{ event.currency || 'points' }}</p>
                            </div>
                        </div>

                        <div v-if="event.rsvp_enabled" class="flex items-start gap-3 p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                            <span class="text-2xl">🎟️</span>
                            <div class="flex-1">
                                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">RSVPs</p>
                                <div class="flex gap-4 text-sm">
                                    <span class="text-green-600 dark:text-green-400">✅ {{ rsvps.going }}</span>
                                    <span class="text-yellow-600 dark:text-yellow-400">🤔 {{ rsvps.maybe }}</span>
                                    <span class="text-red-600 dark:text-red-400">❌ {{ rsvps.not_going }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Dashboard Button -->
                    <div class="mt-6">
                        <a href="/me" class="block w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Right column: Attendees -->
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-neutral-900 dark:text-white mb-4">Attendees ({{ groupMembers.length }})</h2>
                    <div class="space-y-2 max-h-[600px] overflow-y-auto">
                        <div
                            v-for="member in groupMembers"
                            :key="member.id"
                            class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700 rounded"
                        >
                            <div>
                                <p class="text-neutral-900 dark:text-white font-medium">{{ member.name }}</p>
                                <p v-if="member.telegram_username" class="text-sm text-neutral-500 dark:text-neutral-400">@{{ member.telegram_username }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span v-if="member.attended !== null" :class="member.attended ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    {{ member.attended ? '✅ Attended' : '❌ Absent' }}
                                </span>
                                <span v-else :class="rsvpColors[member.rsvp_status]" class="text-sm">
                                    {{ rsvpEmoji[member.rsvp_status] }}
                                    {{ member.rsvp_status === 'pending' ? 'Pending' : member.rsvp_status.charAt(0).toUpperCase() + member.rsvp_status.slice(1).replace('_', ' ') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
