<template>
    <header
        class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
        <nav class="container-custom py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <router-link
                        to="/"
                        class="flex items-center space-x-2 text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <DatabaseIcon class="w-8 h-8" />
                        <span class="text-xl font-bold">Query Play Studio</span>
                    </router-link>

                    <div class="hidden md:flex items-center space-x-4">
                        <router-link
                            v-if="isAuthenticated"
                            to="/sandbox"
                            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="[
                                $route.name === 'sandbox'
                                    ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800',
                            ]">
                            Sandbox
                        </router-link>

                        <router-link
                            v-if="isAuthenticated"
                            to="/exercises"
                            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="[
                                $route.name === 'exercises'
                                    ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800',
                            ]">
                            Exercises
                        </router-link>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <ThemeToggle />

                    <div v-if="isAuthenticated" class="flex items-center space-x-4">
                        <div class="hidden md:block text-sm text-gray-600 dark:text-gray-400">
                            Welcome,
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ username }}
                            </span>
                        </div>

                        <SecondaryButton size="sm" @click="handleLogout">Logout</SecondaryButton>
                    </div>

                    <div v-else class="flex items-center space-x-2">
                        <SecondaryButton size="sm" @click="$router.push('/login')">
                            Sign In
                        </SecondaryButton>

                        <PrimaryButton size="sm" @click="$router.push('/register')">
                            Sign Up
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </nav>
    </header>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import DatabaseIcon from '@icons/DatabaseIcon.vue';
import PrimaryButton from '@components/uikit/buttons/PrimaryButton.vue';
import SecondaryButton from '@components/uikit/buttons/SecondaryButton.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';

const router = useRouter();
const authStore = useAuthStore();

const isAuthenticated = computed(() => authStore.isAuthenticated);
const username = computed(() => authStore.username);

const handleLogout = async () => {
    await authStore.logout();
    router.push('/');
};
</script>
