<template>
    <button
        type="button"
        class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        @click="toggleTheme">
        <ThemeDarkIcon v-if="isDark" class="w-5 h-5" />
        <ThemeLightIcon v-else class="w-5 h-5" />
    </button>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import ThemeDarkIcon from '@icons/ThemeDarkIcon.vue';
import ThemeLightIcon from '@icons/ThemeLightIcon.vue';

const isDark = ref(false);

const toggleTheme = () => {
    isDark.value = !isDark.value;

    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    const storedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    isDark.value = storedTheme === 'dark' || (!storedTheme && prefersDark);

    if (isDark.value) {
        document.documentElement.classList.add('dark');
    }
});
</script>
