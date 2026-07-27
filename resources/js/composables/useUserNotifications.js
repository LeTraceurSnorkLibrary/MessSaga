import {toast} from 'vue3-toastify';

const sleep = (ms) => new Promise((resolve) => {
    setTimeout(resolve, ms);
});

export async function fetchUnreadUserNotifications() {
    const response = await window.axios.get('/api/user/notifications', {
        params: {unread: 1},
    });

    return response.data?.notifications ?? [];
}

export async function markUserNotificationRead(notificationId) {
    await window.axios.post(`/api/user/notifications/${notificationId}/read`);
}

export async function showUnreadUserNotificationsAsToasts() {
    const notifications = await fetchUnreadUserNotifications();

    for (const notification of notifications) {
        toast.warning(notification.message, {autoClose: 8000});
        if (notification.id) {
            await markUserNotificationRead(notification.id);
        }
    }
}

/**
 * Poll for async queue notifications (import / media upload jobs).
 */
export async function pollUserNotifications(options = {}) {
    const attempts = options.attempts ?? 5;
    const intervalMs = options.intervalMs ?? 2000;

    for (let attempt = 0; attempt < attempts; attempt++) {
        await sleep(intervalMs);
        await showUnreadUserNotificationsAsToasts();
    }
}
