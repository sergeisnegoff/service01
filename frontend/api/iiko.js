import client from '@/helpers/api';

export function fetchIikoSettings(config) {
    return client.get('/api/iiko/settings', config);
}
export function setIikoSettings(config) {
    return client.post('/api/iiko/settings', config);
}
export function importIiko(config) {
    return client.post('/api/iiko/import', config);
}
export function sendInvoiceToIiko(id, config) {
    return client.post(`/api/iiko/invoice/${ id }/add`, config);
}
