<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

const props = defineProps<{
    user: {
        taunt_line?: string;
        birthday?: string;
    };
}>();

const emit = defineEmits<{
    updated: [];
}>();

// Profile form
const profileForm = useForm({
    taunt_line: props.user.taunt_line || '',
    birthday: props.user.birthday || '',
});

// Toast notification state
const showToast = ref(false);
const toastType = ref<'success' | 'error'>('success');
const toastMessage = ref('');

function submitProfile() {
    showToast.value = false;

    profileForm.post('/me/profile', {
        preserveScroll: true,
        onSuccess: () => {
            toastType.value = 'success';
            toastMessage.value = 'Profile updated successfully!';
            showToast.value = true;
            emit('updated');
        },
        onError: (errors) => {
            toastType.value = 'error';
            toastMessage.value = 'Failed to update profile. Please try again.';
            showToast.value = true;
        },
    });
}
</script>

<template>
    <div>
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Profile Settings</h3>

        <form @submit.prevent="submitProfile" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Taunt Line
                </label>
                <input
                    v-model="profileForm.taunt_line"
                    type="text"
                    maxlength="255"
                    class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Your victory message to others..."
                />
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                    This message will be displayed when you win a wager
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Birthday
                </label>
                <input
                    v-model="profileForm.birthday"
                    type="date"
                    class="w-full px-3 py-2 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                    The bot will send birthday wishes in your groups
                </p>
            </div>

            <button
                type="submit"
                :disabled="profileForm.processing"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
            >
                <!-- Loading spinner -->
                <svg v-if="profileForm.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ profileForm.processing ? 'Saving...' : 'Save Profile' }}</span>
            </button>
        </form>

        <!-- Toast Notification -->
        <Toast
            :show="showToast"
            :type="toastType"
            :message="toastMessage"
            @close="showToast = false"
        />
    </div>
</template>
