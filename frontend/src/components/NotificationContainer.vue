<template>
    <div class="fixed inset-0 z-50 pointer-events-none">
        <div class="absolute top-4 right-4 w-96 space-y-2">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                class="pointer-events-auto"
                @mouseenter="pauseTimeout(notification.id)"
                @mouseleave="resumeTimeout(notification.id)">
                <div
                    class="card shadow-lg transform transition-all duration-300"
                    :class="[
                        notification.type === 'success' &&
                            'border-success-200 dark:border-success-800',
                        notification.type === 'error' && 'border-danger-200 dark:border-danger-800',
                        notification.type === 'warning' &&
                            'border-warning-200 dark:border-warning-800',
                        notification.type === 'info' &&
                            'border-primary-200 dark:border-primary-800',
                    ]">
                    <div class="flex items-start">
                        <div
                            class="flex-shrink-0 mt-0.5"
                            :class="[
                                notification.type === 'success' &&
                                    'text-success-600 dark:text-success-400',
                                notification.type === 'error' &&
                                    'text-danger-600 dark:text-danger-400',
                                notification.type === 'warning' &&
                                    'text-warning-600 dark:text-warning-400',
                                notification.type === 'info' &&
                                    'text-primary-600 dark:text-primary-400',
                            ]">
                            <component :is="getIcon(notification.type)" class="w-5 h-5" />
                        </div>

                        <div class="ml-3 flex-1">
                            <div
                                v-if="notification.title"
                                class="text-sm font-medium"
                                :class="[
                                    notification.type === 'success' &&
                                        'text-success-900 dark:text-success-300',
                                    notification.type === 'error' &&
                                        'text-danger-900 dark:text-danger-300',
                                    notification.type === 'warning' &&
                                        'text-warning-900 dark:text-warning-300',
                                    notification.type === 'info' &&
                                        'text-primary-900 dark:text-primary-300',
                                ]">
                                {{ notification.title }}
                            </div>

                            <div
                                class="text-sm"
                                :class="[
                                    notification.type === 'success' &&
                                        'text-success-800 dark:text-success-400',
                                    notification.type === 'error' &&
                                        'text-danger-800 dark:text-danger-400',
                                    notification.type === 'warning' &&
                                        'text-warning-800 dark:text-warning-400',
                                    notification.type === 'info' &&
                                        'text-primary-800 dark:text-primary-400',
                                ]">
                                {{ notification.message }}
                            </div>
                        </div>

                        <button
                            type="button"
                            class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                            @click="removeNotification(notification.id)">
                            <span class="sr-only">Close</span>
                            <ClearIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <div
                        v-if="notification.duration && notification.duration > 0"
                        class="absolute bottom-0 left-0 h-1 bg-current opacity-20"
                        :style="{ width: `${getProgressWidth(notification.id)}%` }" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { NotificationType } from '@/stores/notifications';
import { useNotificationStore } from '@/stores/notifications';
import CheckCircleIcon from '@icons/CheckCircleIcon.vue';
import ClearIcon from '@icons/ClearIcon.vue';
import ExclamationTriangleIcon from '@icons/ExclamationTriangleIcon.vue';
import InformationCircleIcon from '@icons/InformationCircleIcon.vue';
import XCircleIcon from '@icons/XCircleIcon.vue';

const notificationStore = useNotificationStore();

const notifications = computed(() => notificationStore.notifications);

const timeouts = new Map<number, number>();
const startTimes = new Map<number, number>();
const remainingTimes = new Map<number, number>();

const getIcon = (type: NotificationType) => {
    switch (type) {
        case 'success':
            return CheckCircleIcon;
        case 'error':
            return XCircleIcon;
        case 'warning':
            return ExclamationTriangleIcon;
        case 'info':
            return InformationCircleIcon;
        default:
            return InformationCircleIcon;
    }
};

const removeNotification = (id: number) => {
    const timeoutId = timeouts.get(id);

    if (timeoutId) {
        clearTimeout(timeoutId);
        timeouts.delete(id);
    }

    startTimes.delete(id);
    remainingTimes.delete(id);
    notificationStore.removeNotification(id);
};

const pauseTimeout = (id: number) => {
    const notification = notifications.value.find(n => n.id === id);

    if (!notification || !notification.duration) {
        return;
    }

    const timeoutId = timeouts.get(id);

    if (timeoutId) {
        clearTimeout(timeoutId);
        timeouts.delete(id);

        const startTime = startTimes.get(id);

        if (startTime) {
            const elapsed = Date.now() - startTime;

            remainingTimes.set(
                id,
                ((notification.duration - elapsed) / notification.duration) * 100,
            );
        }
    }
};

const resumeTimeout = (id: number) => {
    const notification = notifications.value.find(n => n.id === id);

    if (!notification || !notification.duration) {
        return;
    }

    const remainingPercentage = remainingTimes.get(id) || 100;
    const remainingMs = (remainingPercentage / 100) * notification.duration;

    if (remainingMs > 0) {
        startTimes.set(id, Date.now());

        const timeoutId = window.setTimeout(() => {
            removeNotification(id);
        }, remainingMs);

        timeouts.set(id, timeoutId);
    } else {
        removeNotification(id);
    }
};

const getProgressWidth = (id: number): number => {
    const notification = notifications.value.find(n => n.id === id);

    if (!notification || !notification.duration) {
        return 0;
    }

    const startTime = startTimes.get(id);

    if (!startTime) {
        return 100;
    }

    const elapsed = Date.now() - startTime;
    const progress = Math.max(0, 100 - (elapsed / notification.duration) * 100);

    return Math.min(100, Math.max(0, progress));
};

// Set initial start times for notifications
notifications.value.forEach(notification => {
    if (notification.duration && !startTimes.has(notification.id)) {
        startTimes.set(notification.id, Date.now());

        const timeoutId = window.setTimeout(() => {
            removeNotification(notification.id);
        }, notification.duration);

        timeouts.set(notification.id, timeoutId);
    }
});
</script>
