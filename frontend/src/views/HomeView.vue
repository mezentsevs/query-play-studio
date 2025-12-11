<template>
    <div class="min-h-screen flex flex-col">
        <AppHeader />

        <main class="flex-grow container-custom py-12">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-5xl font-bold text-gray-900 dark:text-white mb-6">
                    Query Play Studio
                </h1>

                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">
                    An interactive platform for learning databases with AI assistance. Practice SQL
                    queries across multiple database systems in a safe sandbox environment.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                    <PrimaryButton
                        v-if="!isAuthenticated"
                        size="lg"
                        :icon-left="LoginIcon"
                        @click="router.push('/login')">
                        Sign In
                    </PrimaryButton>

                    <PrimaryButton
                        v-if="!isAuthenticated"
                        size="lg"
                        variant="secondary"
                        :icon-left="RegisterIcon"
                        @click="router.push('/register')">
                        Create Account
                    </PrimaryButton>

                    <PrimaryButton
                        v-if="isAuthenticated"
                        size="lg"
                        @click="router.push('/sandbox')">
                        Go to Sandbox
                    </PrimaryButton>

                    <SecondaryButton
                        v-if="isAuthenticated"
                        size="lg"
                        @click="router.push('/exercises')">
                        View Exercises
                    </SecondaryButton>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                    <div class="card card-hover">
                        <div class="text-primary-600 dark:text-primary-400 mb-4">
                            <DatabaseIcon class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Multi-Database Support</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Practice with MySQL, PostgreSQL, and SQLite. Learn the nuances of each
                            system.
                        </p>
                    </div>

                    <div class="card card-hover">
                        <div class="text-success-600 dark:text-success-400 mb-4">
                            <ExerciseIcon class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Interactive Exercises</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Complete structured exercises with instant feedback and progress
                            tracking.
                        </p>
                    </div>

                    <div class="card card-hover">
                        <div class="text-warning-600 dark:text-warning-400 mb-4">
                            <AiIcon class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-xl font-semibold mb-3">AI Assistant</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Get explanations for errors, query optimization tips, and personalized
                            guidance.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppHeader from '@/components/layout/AppHeader.vue';
import AppFooter from '@/components/layout/AppFooter.vue';
import PrimaryButton from '@components/uikit/buttons/PrimaryButton.vue';
import SecondaryButton from '@components/uikit/buttons/SecondaryButton.vue';
import LoginIcon from '@icons/LoginIcon.vue';
import RegisterIcon from '@icons/RegisterIcon.vue';
import DatabaseIcon from '@icons/DatabaseIcon.vue';
import ExerciseIcon from '@icons/ExerciseIcon.vue';
import AiIcon from '@icons/AiIcon.vue';

const router = useRouter();
const authStore = useAuthStore();
const isAuthenticated = computed(() => authStore.isAuthenticated);
</script>
