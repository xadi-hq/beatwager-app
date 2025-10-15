<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';

interface Props {
    show: boolean;
    type?: 'success' | 'error' | 'info' | 'warning';
    message: string;
    duration?: number;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'info',
    duration: 5000,
});

const emit = defineEmits<{
    close: [];
}>();

const visible = ref(false);
let timeoutId: number | null = null;

watch(() => props.show, (newVal) => {
    if (newVal) {
        visible.value = true;

        // Auto-hide after duration
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => {
            close();
        }, props.duration);
    }
});

const close = () => {
    visible.value = false;
    setTimeout(() => emit('close'), 300); // Wait for animation
};

const typeStyles = {
    success: {
        bg: 'bg-green-50 dark:bg-green-900/20',
        border: 'border-green-200 dark:border-green-800',
        text: 'text-green-800 dark:text-green-200',
        icon: '✓',
    },
    error: {
        bg: 'bg-red-50 dark:bg-red-900/20',
        border: 'border-red-200 dark:border-red-800',
        text: 'text-red-800 dark:text-red-200',
        icon: '✕',
    },
    warning: {
        bg: 'bg-amber-50 dark:bg-amber-900/20',
        border: 'border-amber-200 dark:border-amber-800',
        text: 'text-amber-800 dark:text-amber-200',
        icon: '⚠',
    },
    info: {
        bg: 'bg-blue-50 dark:bg-blue-900/20',
        border: 'border-blue-200 dark:border-blue-800',
        text: 'text-blue-800 dark:text-blue-200',
        icon: 'ℹ',
    },
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="visible"
            :class="[
                'fixed bottom-4 right-4 max-w-md w-full shadow-lg rounded-lg border p-4 flex items-start gap-3 z-50',
                typeStyles[type].bg,
                typeStyles[type].border,
            ]"
        >
            <span :class="['text-xl font-bold', typeStyles[type].text]">
                {{ typeStyles[type].icon }}
            </span>
            <div class="flex-1">
                <p :class="['text-sm font-medium', typeStyles[type].text]">
                    {{ message }}
                </p>
            </div>
            <button
                @click="close"
                :class="['text-lg font-bold hover:opacity-70 transition-opacity', typeStyles[type].text]"
                aria-label="Close"
            >
                ×
            </button>
        </div>
    </Transition>
</template>
