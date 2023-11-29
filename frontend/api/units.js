import client from '@/helpers/api';

export function fetchUnits(config) {
    return client.get('/api/units', config);
}
