<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    modelValue: string[];
    options: string[];
    n: number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();

// Local state for ranking
const selectedRanking = ref<string[]>([...props.modelValue]);

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
    selectedRanking.value = [...newValue];
}, { deep: true });

// Available options (not yet selected)
const availableOptions = computed(() => {
    return props.options.filter(option => !selectedRanking.value.includes(option));
});

// Check if ranking is complete
const isComplete = computed(() => {
    return selectedRanking.value.length === props.n;
});

// Add option to ranking
const addOption = (option: string) => {
    if (selectedRanking.value.length < props.n && !selectedRanking.value.includes(option)) {
        selectedRanking.value.push(option);
        emit('update:modelValue', selectedRanking.value);
    }
};

// Remove option from ranking
const removeOption = (index: number) => {
    selectedRanking.value.splice(index, 1);
    emit('update:modelValue', selectedRanking.value);
};

// Move option up in ranking
const moveUp = (index: number) => {
    if (index > 0) {
        const temp = selectedRanking.value[index];
        selectedRanking.value[index] = selectedRanking.value[index - 1];
        selectedRanking.value[index - 1] = temp;
        emit('update:modelValue', selectedRanking.value);
    }
};

// Move option down in ranking
const moveDown = (index: number) => {
    if (index < selectedRanking.value.length - 1) {
        const temp = selectedRanking.value[index];
        selectedRanking.value[index] = selectedRanking.value[index + 1];
        selectedRanking.value[index + 1] = temp;
        emit('update:modelValue', selectedRanking.value);
    }
};

// Drag and drop state
const draggedIndex = ref<number | null>(null);

const handleDragStart = (index: number) => {
    draggedIndex.value = index;
};

const handleDragOver = (event: DragEvent, index: number) => {
    event.preventDefault();
    if (draggedIndex.value !== null && draggedIndex.value !== index) {
        const draggedItem = selectedRanking.value[draggedIndex.value];
        selectedRanking.value.splice(draggedIndex.value, 1);
        selectedRanking.value.splice(index, 0, draggedItem);
        draggedIndex.value = index;
        emit('update:modelValue', selectedRanking.value);
    }
};

const handleDragEnd = () => {
    draggedIndex.value = null;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Selected Ranking -->
        <div class="space-y-2">
            <div
                v-for="(option, index) in selectedRanking"
                :key="`selected-${index}`"
                draggable="true"
                @dragstart="handleDragStart(index)"
                @dragover="handleDragOver($event, index)"
                @dragend="handleDragEnd"
                class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-300 dark:border-blue-700 rounded-lg cursor-move hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
            >
                <!-- Rank Number -->
                <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-full font-bold text-sm">
                    {{ index + 1 }}
                </div>

                <!-- Option Text -->
                <div class="flex-1 font-medium text-neutral-900 dark:text-white">
                    {{ option }}
                </div>

                <!-- Controls -->
                <div class="flex gap-1">
                    <!-- Move Up -->
                    <button
                        type="button"
                        @click="moveUp(index)"
                        :disabled="index === 0"
                        class="p-1 text-neutral-600 dark:text-neutral-400 hover:text-blue-600 dark:hover:text-blue-400 disabled:opacity-30 disabled:cursor-not-allowed"
                        :aria-label="`Move ${option} up`"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>

                    <!-- Move Down -->
                    <button
                        type="button"
                        @click="moveDown(index)"
                        :disabled="index === selectedRanking.length - 1"
                        class="p-1 text-neutral-600 dark:text-neutral-400 hover:text-blue-600 dark:hover:text-blue-400 disabled:opacity-30 disabled:cursor-not-allowed"
                        :aria-label="`Move ${option} down`"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Remove -->
                    <button
                        type="button"
                        @click="removeOption(index)"
                        class="p-1 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                        :aria-label="`Remove ${option}`"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="selectedRanking.length === 0"
                class="p-6 border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-lg text-center text-neutral-500 dark:text-neutral-400"
            >
                <p class="text-sm">Select {{ n }} options from below to create your ranking</p>
            </div>

            <!-- Progress Indicator -->
            <div v-if="selectedRanking.length > 0 && selectedRanking.length < n" class="text-sm text-neutral-600 dark:text-neutral-400">
                {{ selectedRanking.length }} / {{ n }} selected
            </div>

            <!-- Complete Indicator -->
            <div v-if="isComplete" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Ranking complete!</span>
            </div>
        </div>

        <!-- Available Options -->
        <div v-if="availableOptions.length > 0 && !isComplete">
            <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Available Options:
            </p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="option in availableOptions"
                    :key="`available-${option}`"
                    type="button"
                    @click="addOption(option)"
                    class="px-3 py-2 bg-neutral-100 dark:bg-neutral-700 text-neutral-900 dark:text-white rounded-md hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors border border-neutral-300 dark:border-neutral-600"
                >
                    {{ option }}
                </button>
            </div>
        </div>
    </div>
</template>
