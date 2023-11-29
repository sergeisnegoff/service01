import client from '@/helpers/api';

export function productsImportParse(data, config) {
    return client.post('/api/productsImport/parse', data, config);
}
export function fetchProductsImportMappingFields(config) {
    return client.get('/api/productsImport/mappingFields', config);
}
export function productsImport(id, data, config) {
    return client.post(`/api/productsImport/${ id }`, data, config);
}
export function setProductsImportMapping(id, data, config) {
    return client.put(`/api/productsImport/${ id }/mapping`, data, config);
}
