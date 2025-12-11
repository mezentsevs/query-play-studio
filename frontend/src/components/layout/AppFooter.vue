<template>
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
        <div class="container-custom py-8">
            <div
                class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
                <div class="flex items-center space-x-2">
                    <DatabaseIcon class="w-6 h-6 text-gray-900 dark:text-white" />
                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                        Query Play Studio
                    </span>
                </div>

                <div class="flex items-center space-x-6">
                    <router-link
                        to="/privacy"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        Privacy Policy
                    </router-link>
                    <router-link
                        to="/terms"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        Terms of Service
                    </router-link>
                    <router-link
                        to="/contact"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        Contact
                    </router-link>
                </div>

                <div class="text-sm text-gray-500 dark:text-gray-400">
                    © {{ currentYear }} Query Play Studio. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import DatabaseIcon from '@icons/DatabaseIcon.vue';

const currentYear = computed(() => new Date().getFullYear());
</script>
