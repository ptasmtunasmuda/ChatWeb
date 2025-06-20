import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref([]);

    const addNotification = (notification) => {
        const id = Date.now() + Math.random();
        const newNotification = {
            id,
            type: notification.type || 'info',
            title: notification.title || '',
            message: notification.message || '',
            duration: notification.duration || 5000
        };

        notifications.value.push(newNotification);

        // Auto remove notification after duration
        if (newNotification.duration > 0) {
            setTimeout(() => {
                removeNotification(id);
            }, newNotification.duration);
        }

        return id;
    };

    const removeNotification = (id) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index > -1) {
            notifications.value.splice(index, 1);
        }
    };

    const clearAll = () => {
        notifications.value = [];
    };

    // Convenience methods
    const success = (title, message, duration) => {
        return addNotification({ type: 'success', title, message, duration });
    };

    const error = (title, message, duration) => {
        return addNotification({ type: 'error', title, message, duration });
    };

    const info = (title, message, duration) => {
        return addNotification({ type: 'info', title, message, duration });
    };

    const warning = (title, message, duration) => {
        return addNotification({ type: 'warning', title, message, duration });
    };

    return {
        notifications,
        addNotification,
        removeNotification,
        clearAll,
        success,
        error,
        info,
        warning
    };
});
