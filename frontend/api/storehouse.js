import client from '@/helpers/api';

export function fetchStorehouseSettings(config) {
    return client.get('/api/storeHouse/settings', config);
}
export function setStorehouseSettings(config) {
    return client.post('/api/storeHouse/settings', config);
}
export function importStorehouse(config) {
    return client.post('/api/storeHouse/import', config);
}
