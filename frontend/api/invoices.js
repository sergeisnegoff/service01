import client from '@/helpers/api';

export function createInvoice(data, config) {
    return client.post('/api/invoices', data, config);
}
export function fetchInvoices(data, config) {
    return client.get('/api/invoices', data, config);
}
export function fetchInvoice(id, data, config) {
    return client.get(`/api/invoices/${ id }`, data, config);
}
export function fetchInvoiceStatuses(config) {
    return client.get('/api/invoices/statuses', config);
}
export function fetchInvoiceColumns(config) {
    return client.get('/api/invoices/columns', config);
}
export function sendInvoiceColumns(config) {
    return client.post('/api/invoices/columns', config);
}
export function sendInvoiceComparison(id, data, config) {
    return client.post(`/api/invoices/${ id }/comparison`, data, config);
}
export function sendInvoiceAccept(id, data, config) {
    return client.post(`/api/invoices/${ id }/accept`, data, config);
}
export function exchangeInvoice(id, config) {
    return client.post(`/api/invoices/${ id }/exchange`, config);
}
