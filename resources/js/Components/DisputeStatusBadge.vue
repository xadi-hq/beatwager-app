<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    status: 'pending' | 'resolved' | 'disputed';
    resolution?: 'original_correct' | 'fraud_confirmed' | 'premature_settlement' | null;
    disputeId?: string;
    size?: 'sm' | 'md' | 'lg';
    clickable?: boolean;
}>();

const sizeClasses = computed(() => {
    switch (props.size || 'md') {
        case 'sm': return 'text-xs px-2 py-0.5';
        case 'lg': return 'text-base px-4 py-2';
        default: return 'text-sm px-3 py-1';
    }
});

const statusConfig = computed(() => {
    // Handle "disputed" status (item is currently being disputed)
    if (props.status === 'disputed') {
        return {
            icon: '⚠️',
            text: 'Disputed',
            bgClass: 'bg-amber-100 dark:bg-amber-900/30',
            textClass: 'text-amber-700 dark:text-amber-300',
            borderClass: 'border-amber-300 dark:border-amber-700',
        };
    }

    // Handle resolved status
    if (props.status === 'resolved') {
        switch (props.resolution) {
            case 'original_correct':
                return {
                    icon: '✅',
                    text: 'Dispute Dismissed',
                    bgClass: 'bg-green-100 dark:bg-green-900/30',
                    textClass: 'text-green-700 dark:text-green-300',
                    borderClass: 'border-green-300 dark:border-green-700',
                };
            case 'fraud_confirmed':
                return {
                    icon: '🚨',
                    text: 'Fraud Confirmed',
                    bgClass: 'bg-red-100 dark:bg-red-900/30',
                    textClass: 'text-red-700 dark:text-red-300',
                    borderClass: 'border-red-300 dark:border-red-700',
                };
            case 'premature_settlement':
                return {
                    icon: '⏰',
                    text: 'Premature Settlement',
                    bgClass: 'bg-purple-100 dark:bg-purple-900/30',
                    textClass: 'text-purple-700 dark:text-purple-300',
                    borderClass: 'border-purple-300 dark:border-purple-700',
                };
            default:
                return {
                    icon: '❓',
                    text: 'Resolved',
                    bgClass: 'bg-neutral-100 dark:bg-neutral-700',
                    textClass: 'text-neutral-700 dark:text-neutral-300',
                    borderClass: 'border-neutral-300 dark:border-neutral-600',
                };
        }
    }

    // Pending status (dispute awaiting votes)
    return {
        icon: '⚖️',
        text: 'Awaiting Verdict',
        bgClass: 'bg-blue-100 dark:bg-blue-900/30',
        textClass: 'text-blue-700 dark:text-blue-300',
        borderClass: 'border-blue-300 dark:border-blue-700',
    };
});
</script>

<template>
    <Link
        v-if="clickable && disputeId"
        :href="`/disputes/${disputeId}`"
        class="inline-flex items-center gap-1.5 rounded-full border font-medium transition-opacity hover:opacity-80"
        :class="[sizeClasses, statusConfig.bgClass, statusConfig.textClass, statusConfig.borderClass]"
    >
        <span>{{ statusConfig.icon }}</span>
        <span>{{ statusConfig.text }}</span>
    </Link>
    <span
        v-else
        class="inline-flex items-center gap-1.5 rounded-full border font-medium"
        :class="[sizeClasses, statusConfig.bgClass, statusConfig.textClass, statusConfig.borderClass]"
    >
        <span>{{ statusConfig.icon }}</span>
        <span>{{ statusConfig.text }}</span>
    </span>
</template>
