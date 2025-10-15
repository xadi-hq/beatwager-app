<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';

// Ziggy route helper is available globally as route()
// Example: route('dashboard') or route('users.show', { id: 1 })
</script>

<template>
    <AppLayout title="Welcome">
        <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-neutral-900 dark:text-neutral-100">
                <h1 class="text-3xl font-semibold mb-4">Welcome to BeatWager</h1>
                <p class="text-lg text-neutral-600 dark:text-neutral-400 mb-6">
                    A platform-agnostic social wagering system for friend groups.
                </p>
                
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <h3 class="font-semibold mb-2">📱 Telegram Integration</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            Create and join wagers directly from Telegram
                        </p>
                    </div>
                    
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <h3 class="font-semibold mb-2">💰 Point Economy</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            Track points, manage balances, audit transactions
                        </p>
                    </div>
                    
                    <div class="p-4 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                        <h3 class="font-semibold mb-2">🏆 Seasons</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            Create competitive seasons with leaderboards
                        </p>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-3">Tech Stack</h2>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">Laravel 12</span>
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm">Vue 3</span>
                        <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full text-sm">Inertia.js</span>
                        <span class="px-3 py-1 bg-pink-100 dark:bg-pink-900 text-pink-800 dark:text-pink-200 rounded-full text-sm">TypeScript</span>
                        <span class="px-3 py-1 bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200 rounded-full text-sm">Tailwind CSS</span>
                        <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full text-sm">PostgreSQL 17</span>
                        <span class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full text-sm">Redis 8</span>
                        <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 rounded-full text-sm">Ziggy</span>
                    </div>
                </div>

                <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <h3 class="font-semibold mb-2 text-blue-900 dark:text-blue-200">✅ Ziggy Configured</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Laravel routes are now available in Vue components via the <code class="px-1 py-0.5 bg-blue-100 dark:bg-blue-900 rounded">route()</code> helper.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
