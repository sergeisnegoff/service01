import client from '@/helpers/api';

export function fetchNotifications(config) {
    return client.get('/api/notifications', config);
}
export function fetchUnreadNotificationsCount(config) {
    return client.get('/api/notifications/unread', config);
}
export function markNotificationAsRead(config) {
    return client.put('/api/notifications/read', config);
}
