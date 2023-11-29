import client from '@/helpers/api';

export function fetchWarehouses(config) {
    return client.get('/api/warehouses/self', config);
}
