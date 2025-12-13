<template>
    <div
        class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="flex justify-center">
                    <DatabaseIcon class="w-12 h-12 text-primary-600 dark:text-primary-400" />
                </div>

                <h2 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
                    Sign in to your account
                </h2>

                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Or
                    <router-link
                        to="/register"
                        class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">
                        create a new account
                    </router-link>
                </p>
            </div>

            <div class="card">
                <form class="space-y-6" @submit.prevent="handleLogin">
                    <TextInput
                        v-model="email"
                        label="Email address"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="you@example.com"
                        :error="errors.email"
                        @blur="validateField('email')" />

                    <PasswordInput
                        v-model="password"
                        label="Password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        :error="errors.password"
                        @blur="validateField('password')" />

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember-me"
                                v-model="rememberMe"
                                type="checkbox"
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
                            <label
                                for="remember-me"
                                class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                Remember me
                            </label>
                        </div>

                        <router-link
                            to="#"
                            class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">
                            Forgot your password?
                        </router-link>
                    </div>

                    <div>
                        <PrimaryButton
                            type="submit"
                            class="w-full"
                            :loading="authStore.loading"
                            :disabled="!isFormValid">
                            Sign in
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
const password = ref('');
const rememberMe = ref(false);
const errors = ref({
    email: '',
    password: '',
});

const isFormValid = computed(() => {
    return (
        email.value.trim() && password.value.trim() && !errors.value.email && !errors.value.password
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
        case 'password':
            if (!password.value.trim()) {
                errors.value.password = 'Password is required';
            } else if (password.value.length < 6) {
                errors.value.password = 'Password must be at least 6 characters';
            } else {
                errors.value.password = '';
            }

            break;
    }
};

const handleLogin = async () => {
    validateField('email');
    validateField('password');

    if (errors.value.email || errors.value.password) {
        return;
    }

    const result = await authStore.login(email.value, password.value);

    if (result.success) {
        const redirect = router.currentRoute.value.query.redirect as string;

        router.push(redirect || '/sandbox');
    }
};

onMounted(() => {
    authStore.clearError();
});
</script>
