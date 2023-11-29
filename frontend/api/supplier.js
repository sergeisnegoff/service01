import client from '@/helpers/api';

export function sendVerificationRequest(config) {
    return client.post('/api/suppliers/self/verificationRequest', config);
}
export function fetchSuppliersList(config) {
    return client.get('/api/suppliers', config);
}
export function sendJobRequest(supplierId, config) {
    return client.post(`/api/suppliers/${ supplierId }/jobRequests`, config);
}
