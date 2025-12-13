<template>
    <div
        class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
        <div class="text-center">
            <h1 class="text-9xl font-bold text-primary-600 dark:text-primary-400">404</h1>

            <h2 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">Page not found</h2>

            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                Sorry, we couldn't find the page you're looking for.
            </p>

            <div class="mt-6">
                <PrimaryButton @click="$router.push('/')">Go back home</PrimaryButton>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import PrimaryButton from '@components/uikit/buttons/PrimaryButton.vue';
</script>
