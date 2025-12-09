<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
    ogImage?: string;
}>();

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

    // Smooth scroll behavior
    document.documentElement.style.scrollBehavior = 'smooth';
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

function scrollToSection(sectionId: string) {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>

<template>
    <div>
        <Head :title="title">
            <meta v-if="description" name="description" :content="description" />
            <meta v-if="description" property="og:description" :content="description" />
            <meta v-if="title" property="og:title" :content="title" />
            <meta property="og:type" content="website" />
            <meta v-if="ogImage" property="og:image" :content="ogImage" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta v-if="title" name="twitter:title" :content="title" />
            <meta v-if="description" name="twitter:description" :content="description" />
        </Head>

        <div class="min-h-screen bg-white dark:bg-neutral-900">
            <!-- Fixed Navigation -->
            <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-neutral-900/80 backdrop-blur-md border-b border-neutral-200 dark:border-neutral-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <!-- Left: Logo/Brand -->
                        <div class="flex items-center">
                            <button
                                @click="scrollToSection('hero')"
                                class="text-xl font-bold text-neutral-900 dark:text-neutral-100 hover:text-[#5da7f8] dark:hover:text-[#5da7f8] transition-colors"
                            >
                                WagerBot
                            </button>
                        </div>

                        <!-- Center: Navigation Links -->
                        <div class="flex items-center gap-6">
                            <button
                                @click="scrollToSection('about')"
                                class="text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-[#5da7f8] dark:hover:text-[#5da7f8] transition-colors"
                            >
                                What is this?
                            </button>
                            <button
                                @click="scrollToSection('donate')"
                                class="text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-[#5da7f8] dark:hover:text-[#5da7f8] transition-colors"
                            >
                                Support
                            </button>
                        </div>

                        <!-- Right: Dark mode toggle -->
                        <div class="flex items-center">
                            <button
                                @click="toggleDarkMode"
                                class="p-2 rounded-lg text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
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
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content (no padding constraints) -->
            <main class="pt-16">
                <slot />
            </main>
        </div>
    </div>
</template>
