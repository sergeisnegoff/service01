import client from '@/helpers/api';

export function fetchProductsExport(config) {
    return client.get('/api/productsExport', config);
}
export function fetchProductsExportFields(config) {
    return client.get('/api/productsExport/fields', config);
}

