import client from '@/helpers/api';

export function fetchCounterparties(config) {
    return client.get('/api/counterparties/self', config);
}
