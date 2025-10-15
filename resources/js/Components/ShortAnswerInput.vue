<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    modelValue: string;
    maxLength?: number;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const maxChars = computed(() => props.maxLength || 100);
const remainingChars = computed(() => maxChars.value - props.modelValue.length);
const charWarning = computed(() => remainingChars.value < 20);

const handleInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <div class="space-y-2">
        <textarea
            :value="modelValue"
            @input="handleInput"
            :maxlength="maxChars"
            :placeholder="placeholder || 'Enter your answer...'"
            rows="3"
            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        ></textarea>

        <div class="flex justify-between items-center text-sm">
            <p class="text-neutral-500 dark:text-neutral-400">
                Free text answer (max {{ maxChars }} characters)
            </p>
            <p
                class="font-medium"
                :class="charWarning ? 'text-amber-600 dark:text-amber-400' : 'text-neutral-500 dark:text-neutral-400'"
            >
                {{ remainingChars }} characters remaining
            </p>
        </div>
    </div>
</template>
