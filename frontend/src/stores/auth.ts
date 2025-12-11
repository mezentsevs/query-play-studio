import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

import authService from '@/services/auth';
import type { User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(authService.getStoredUser());
    const loading = ref(false);
    const error = ref<string | null>(null);

    const isAuthenticated = computed(() => !!user.value);
    const username = computed(() => user.value?.username || '');
    const email = computed(() => user.value?.email || '');

    async function login(email: string, password: string) {
        loading.value = true;
        error.value = null;

        try {
            const response = await authService.login(email, password);

            if (response.status === 'success') {
                user.value = response.user;
                return { success: true };
            } else {
                error.value = response.message;
                return { success: false, error: response.message };
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || err.message || 'Login failed';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }

    async function register(email: string, password: string, username: string) {
        loading.value = true;
        error.value = null;

        try {
            const response = await authService.register(email, password, username);

            if (response.status === 'success') {
                user.value = response.user;
                return { success: true };
            } else {
                error.value = response.message;
                return { success: false, error: response.message };
            }
        } catch (err: any) {
            error.value = err.response?.data?.message || err.message || 'Registration failed';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        loading.value = true;
        try {
            await authService.logout();
            user.value = null;
            error.value = null;
        } finally {
            loading.value = false;
        }
    }

    async function fetchUser() {
        loading.value = true;
        try {
            const fetchedUser = await authService.getCurrentUser();
            if (fetchedUser) {
                user.value = fetchedUser;
            }
        } finally {
            loading.value = false;
        }
    }

    function clearError() {
        error.value = null;
    }

    return {
        user,
        loading,
        error,
        isAuthenticated,
        username,
        email,
        login,
        register,
        logout,
        fetchUser,
        clearError,
    };
});
