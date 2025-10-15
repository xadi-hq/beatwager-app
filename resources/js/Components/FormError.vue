<script setup lang="ts">
defineProps<{
    error?: string | string[];
}>();
</script>

<template>
    <p v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400">
        {{ Array.isArray(error) ? error[0] : error }}
    </p>
</template>
