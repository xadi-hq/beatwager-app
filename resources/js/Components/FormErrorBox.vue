<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    errors: Record<string, string | string[]>;
    exclude?: string[];
}>();

const hasErrors = computed(() => {
    const errorKeys = Object.keys(props.errors);
    const excludeList = props.exclude || [];
    return errorKeys.some(key => !excludeList.includes(key));
});

const firstError = computed(() => {
    const errorKeys = Object.keys(props.errors);
    const excludeList = props.exclude || [];

    for (const key of errorKeys) {
        if (!excludeList.includes(key)) {
            const error = props.errors[key];
            return Array.isArray(error) ? error[0] : error;
        }
    }
    return '';
});
</script>

<template>
    <div v-if="hasErrors" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
        <p class="text-sm text-red-800 dark:text-red-200 font-medium">
            {{ firstError }}
        </p>
    </div>
</template>
