import { defineStore } from 'pinia';
import { ref } from 'vue';

export type NotificationType = 'success' | 'error' | 'warning' | 'info';

export interface Notification {
    id: number;
    type: NotificationType;
    title?: string;
    message: string;
    duration?: number;
}

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref<Notification[]>([]);
    let nextId = 1;

    const addNotification = (
        type: NotificationType,
        message: string,
        title?: string,
        duration: number = 3000,
    ) => {
        const notification: Notification = {
            id: nextId++,
            type,
            title,
            message,
            duration,
        };

        notifications.value.push(notification);

        if (duration > 0) {
            setTimeout(() => {
                removeNotification(notification.id);
            }, duration);
        }

        return notification.id;
    };

    const removeNotification = (id: number) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            notifications.value.splice(index, 1);
        }
    };

    const clearNotifications = () => {
        notifications.value = [];
    };

    return {
        notifications,
        addNotification,
        removeNotification,
        clearNotifications,
    };
});
