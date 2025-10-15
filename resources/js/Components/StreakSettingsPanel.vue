<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    groupId: string;
    pointsCurrencyName: string;
}>();

const emit = defineEmits<{
    saved: [];
}>();

interface MultiplierTier {
    min: number;
    max: number | null;
    multiplier: number;
}

interface StreakConfig {
    enabled: boolean;
    multiplier_tiers: MultiplierTier[];
}

const config = ref<StreakConfig>({
    enabled: false,
    multiplier_tiers: [
        { min: 1, max: 3, multiplier: 1.0 },
        { min: 4, max: 6, multiplier: 1.2 },
        { min: 7, max: 9, multiplier: 1.4 },
        { min: 10, max: 19, multiplier: 1.5 },
        { min: 20, max: null, multiplier: 2.0 },
    ],
});

const loading = ref(true);
const error = ref<string | null>(null);

// Use Inertia form for saving (handles CSRF automatically)
const form = useForm<StreakConfig>({
    enabled: false,
    multiplier_tiers: [],
});

onMounted(async () => {
    try {
        const response = await fetch(`/groups/${props.groupId}/streak-config`);
        if (response.ok) {
            const data = await response.json();
            if (data) {
                config.value = data;
                // Sync form with loaded config
                form.enabled = data.enabled;
                form.multiplier_tiers = data.multiplier_tiers;
            }
        }
    } catch (e) {
        console.error('Failed to load streak config:', e);
    } finally {
        loading.value = false;
    }
});

const addTier = () => {
    const lastTier = config.value.multiplier_tiers[config.value.multiplier_tiers.length - 1];
    if (!lastTier) return;

    const newMin = lastTier.max ? lastTier.max + 1 : (lastTier.min + 10);

    config.value.multiplier_tiers.push({
        min: newMin,
        max: null,
        multiplier: 1.0,
    });
};

const removeTier = (index: number) => {
    if (config.value.multiplier_tiers.length > 1) {
        config.value.multiplier_tiers.splice(index, 1);
    }
};

const saveConfig = () => {
    error.value = null;

    // Sync current config to form
    form.enabled = config.value.enabled;
    form.multiplier_tiers = config.value.multiplier_tiers;

    // Use Inertia form post (handles CSRF automatically)
    form.post(`/groups/${props.groupId}/streak-config`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
        },
        onError: (errors: any) => {
            error.value = errors.error || 'Failed to save configuration';
        },
    });
};

const getExampleBonus = (multiplier: number): number => {
    return Math.round(100 * multiplier);
};
</script>

<template>
    <div class="space-y-6">
        <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Loading streak settings...</p>
        </div>

        <div v-else>
            <div>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Event Attendance Streaks</h3>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                    Reward users who consistently attend events with bonus multipliers
                </p>
            </div>

            <!-- Enable/Disable Toggle -->
            <div class="flex items-center justify-between py-4 border-b border-neutral-200 dark:border-neutral-700">
                <div>
                    <label class="text-sm font-medium text-neutral-900 dark:text-neutral-100">Enable Streak Bonuses</label>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        When enabled, users earn multiplied bonuses for consecutive event attendance
                    </p>
                </div>
                <button
                    @click="config.enabled = !config.enabled"
                    :class="[
                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                        config.enabled
                            ? 'bg-blue-600'
                            : 'bg-neutral-200 dark:bg-neutral-700'
                    ]"
                >
                    <span
                        :class="[
                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                            config.enabled ? 'translate-x-6' : 'translate-x-1'
                        ]"
                    />
                </button>
            </div>

            <!-- Multiplier Tiers -->
            <div v-if="config.enabled" class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 mb-2">Bonus Multiplier Tiers</h4>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Define how attendance bonuses scale with streak length. Base bonus gets multiplied by these values.
                    </p>
                </div>

                <div v-for="(tier, index) in config.multiplier_tiers" :key="index"
                     class="grid grid-cols-12 gap-3 items-start p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                    <!-- Attendance Range -->
                    <div class="col-span-5">
                        <label class="text-xs text-neutral-600 dark:text-neutral-400 block mb-1">Attendance Count</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="number"
                                v-model.number="tier.min"
                                min="1"
                                class="w-full px-2 py-1 text-sm bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Min"
                            />
                            <span class="text-neutral-600 dark:text-neutral-400">-</span>
                            <input
                                type="number"
                                v-model.number="tier.max"
                                :min="tier.min"
                                class="w-full px-2 py-1 text-sm bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Max (∞)"
                            />
                        </div>
                    </div>

                    <!-- Multiplier -->
                    <div class="col-span-3">
                        <label class="text-xs text-neutral-600 dark:text-neutral-400 block mb-1">Multiplier</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="number"
                                v-model.number="tier.multiplier"
                                step="0.1"
                                min="1.0"
                                max="5.0"
                                class="w-full px-2 py-1 text-sm bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <span class="text-xs text-neutral-600 dark:text-neutral-400">×</span>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="col-span-2">
                        <label class="text-xs text-neutral-600 dark:text-neutral-400 block mb-1">Example</label>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400">
                            100 → <span class="font-semibold text-blue-600 dark:text-blue-400">{{ getExampleBonus(tier.multiplier) }}</span>
                        </div>
                    </div>

                    <!-- Delete Button -->
                    <div class="col-span-1 flex items-end h-full pb-1">
                        <button
                            @click="removeTier(index)"
                            :disabled="config.multiplier_tiers.length === 1"
                            :class="[
                                'p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors',
                                config.multiplier_tiers.length === 1
                                    ? 'opacity-50 cursor-not-allowed'
                                    : 'text-red-600 dark:text-red-400'
                            ]"
                            title="Remove tier"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button
                    @click="addTier"
                    class="w-full py-2 px-3 text-sm text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
                >
                    + Add Tier
                </button>

                <!-- Info Box -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-blue-800 dark:text-blue-200">
                            <p class="font-medium mb-1">How streaks work:</p>
                            <ul class="space-y-1 list-disc list-inside">
                                <li>Streaks increment when users attend consecutive events</li>
                                <li>Missing an event resets the streak to 0</li>
                                <li>The multiplier applies to the base attendance bonus</li>
                                <li>Example: 3rd event in a row with 1.4× = 100 {{ pointsCurrencyName}} becomes 140 {{ pointsCurrencyName }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                <p class="text-sm text-red-800 dark:text-red-200">{{ error }}</p>
            </div>

            <!-- Save Button -->
            <button
                @click="saveConfig"
                :disabled="form.processing"
                class="w-full py-2 px-4 mt-2 bg-blue-600 hover:bg-blue-700 disabled:bg-neutral-400 text-white font-medium rounded-lg transition-colors"
            >
                {{ form.processing ? 'Saving...' : 'Save Streak Settings' }}
            </button>

            <!-- Change Audit Note -->
            <p class="text-xs text-neutral-500 dark:text-neutral-400 text-center">
                Changes will be logged in the audit log with your username
            </p>
        </div>
    </div>
</template>
