<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps<{
    title?: string;
}>();

const page = usePage();
const user = page.props.user as any;

// Dark mode management
const isDark = ref(false);

onMounted(() => {
    // Check if user has dark mode preference or system preference
    isDark.value = localStorage.getItem('theme') === 'dark' ||
        (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);

    // Apply dark mode class
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    }
});

function toggleDarkMode() {
    isDark.value = !isDark.value;

    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
}
</script>

<template>
    <div>
        <Head :title="title" />

        <div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
            <!-- Navigation -->
            <nav class="bg-white dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <!-- Left: Logo/Brand -->
                        <div class="flex items-center">
                            <Link href="/me" class="text-xl font-semibold text-neutral-900 dark:text-neutral-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                BeatWager
                            </Link>
                        </div>

                        <!-- Right: User info + Dark mode toggle -->
                        <div class="flex items-center gap-4">
                            <!-- Dark Mode Toggle -->
                            <button
                                @click="toggleDarkMode"
                                class="p-2 rounded-lg text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors"
                                :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                            >
                                <!-- Sun icon (light mode) -->
                                <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <!-- Moon icon (dark mode) -->
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </button>

                            <!-- User Info -->
                            <div v-if="user" class="flex items-center gap-2">
                                <div class="text-sm">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ user.name || (user.telegram_username ? `@${user.telegram_username}` : 'User') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="py-8">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
