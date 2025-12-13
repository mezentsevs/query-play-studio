<template>
    <div
        class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="flex justify-center">
                    <DatabaseIcon class="w-12 h-12 text-primary-600 dark:text-primary-400" />
                </div>

                <h2 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                    Create a new account
                </h2>

                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Or
                    <router-link
                        to="/login"
                        class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">
                        sign in to existing account
                    </router-link>
                </p>
            </div>

            <div class="card">
                <form class="space-y-6" @submit.prevent="handleRegister">
                    <TextInput
                        v-model="email"
                        label="Email address"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="you@example.com"
                        :error="errors.email"
                        @blur="validateField('email')" />

                    <TextInput
                        v-model="username"
                        label="Username"
                        type="text"
                        required
                        autocomplete="username"
                        placeholder="john_doe"
                        :error="errors.username"
                        @blur="validateField('username')" />

                    <PasswordInput
                        v-model="password"
                        label="Password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        :error="errors.password"
                        @blur="validateField('password')" />

                    <PasswordInput
                        v-model="confirmPassword"
                        label="Confirm Password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        :error="errors.confirmPassword"
                        @blur="validateField('confirmPassword')" />

                    <div class="flex items-center">
                        <input
                            id="terms"
                            v-model="acceptedTerms"
                            type="checkbox"
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                            required />
                        <label
                            for="terms"
                            class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            I agree to the
                            <router-link
                                to="#"
                                class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">
                                Terms of Service
                            </router-link>
                            and
                            <router-link
                                to="#"
                                class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">
                                Privacy Policy
                            </router-link>
                        </label>
                    </div>

                    <div>
                        <PrimaryButton
                            type="submit"
                            class="w-full"
                            :loading="authStore.loading"
                            :disabled="!isFormValid">
                            Create Account
                        </PrimaryButton>
                    </div>
                </form>

                <div
                    v-if="authStore.error"
                    class="mt-4 p-3 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                    <p class="text-sm text-danger-800 dark:text-danger-300">
                        {{ authStore.error }}
                    </p>
                </div>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600" />
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span
                                class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                Or continue with
                            </span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <SecondaryButton class="w-full" @click="$router.push('/sandbox')">
                            Try Sandbox as Guest
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import DatabaseIcon from '@icons/DatabaseIcon.vue';
import PasswordInput from '@components/uikit/inputs/PasswordInput.vue';
import PrimaryButton from '@components/uikit/buttons/PrimaryButton.vue';
import SecondaryButton from '@components/uikit/buttons/SecondaryButton.vue';
import TextInput from '@components/uikit/inputs/TextInput.vue';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const username = ref('');
const password = ref('');
const confirmPassword = ref('');
const acceptedTerms = ref(false);
const errors = ref({
    email: '',
    username: '',
    password: '',
    confirmPassword: '',
});

const isFormValid = computed(() => {
    return (
        email.value.trim() &&
        username.value.trim() &&
        password.value.trim() &&
        confirmPassword.value.trim() &&
        acceptedTerms.value &&
        !errors.value.email &&
        !errors.value.username &&
        !errors.value.password &&
        !errors.value.confirmPassword
    );
});

const validateField = (field: string) => {
    switch (field) {
        case 'email':
            if (!email.value.trim()) {
                errors.value.email = 'Email is required';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                errors.value.email = 'Please enter a valid email address';
            } else {
                errors.value.email = '';
            }

            break;
        case 'username':
            if (!username.value.trim()) {
                errors.value.username = 'Username is required';
            } else if (username.value.length < 3) {
                errors.value.username = 'Username must be at least 3 characters';
            } else if (!/^[a-zA-Z0-9_]+$/.test(username.value)) {
                errors.value.username =
                    'Username can only contain letters, numbers and underscores';
            } else {
                errors.value.username = '';
            }

            break;
        case 'password':
            if (!password.value.trim()) {
                errors.value.password = 'Password is required';
            } else if (password.value.length < 6) {
                errors.value.password = 'Password must be at least 6 characters';
            } else {
                errors.value.password = '';

                if (confirmPassword.value.trim()) {
                    validateField('confirmPassword');
                }
            }

            break;
        case 'confirmPassword':
            if (!confirmPassword.value.trim()) {
                errors.value.confirmPassword = 'Please confirm your password';
            } else if (confirmPassword.value !== password.value) {
                errors.value.confirmPassword = 'Passwords do not match';
            } else {
                errors.value.confirmPassword = '';
            }

            break;
    }
};

const handleRegister = async () => {
    validateField('email');
    validateField('username');
    validateField('password');
    validateField('confirmPassword');

    if (Object.values(errors.value).some(error => error)) {
        return;
    }

    const result = await authStore.register(email.value, password.value, username.value);

    if (result.success) {
        const redirect = router.currentRoute.value.query.redirect as string;

        router.push(redirect || '/sandbox');
    }
};

onMounted(() => {
    authStore.clearError();
});
</script>
