import client from '@/helpers/api';

export function fetchMercurySettings(config) {
    return client.get('/api/mercuries/settings', config);
}
export function setMercurySettings(config) {
    return client.post('/api/mercuries/settings', config);
}
export function addMercuryDoctor(data, config) {
    return client.post('/api/mercuries/doctors', data, config);
}
export function changeMercuryDoctor(id, data, config) {
    return client.put(`/api/mercuries/doctors/${ id }`, data, config);
}
export function deleteMercuryDoctor(id, data, config) {
    return client.delete(`/api/mercuries/doctors/${ id }`, data, config);
}
export function fetchMercuryDoctors(config) {
    return client.get('/api/mercuries/doctors', config);
}
export function fetchVSD(config) {
    return client.get('/api/mercuries/documents', config);
}
export function fetchVSDFilter(config) {
    return client.get('/api/mercuries/documents/filter', config);
}
export function fetchMercuryColumns(config) {
    return client.get('/api/mercuries/columns', config);
}
export function sendMercuryColumns(config) {
    return client.post('/api/mercuries/columns', config);
}
export function setVSDRepayment(config) {
    return client.post('/api/mercuries/settings/autoRepayments', config);
}
export function sendProblems(data, config) {
    return client.post('/api/mercuries/documents/problems', data, config);
}
/**
 *
 * @param {Object} config  Погасить ВСД
 * @property {Array} documentsId - GUID ВСД
 * @property {Boolean} unredeemed - погасить непогашенные ВСД
 * @return {Promise}
 */
export function repayVSD(config) {
    return client.post('/api/mercuries/documents/extinguish', config);
}
export function updateVSDList(config) {
    return client.post('/api/mercuries/documents/update', config);
}
export function updateVSDStatus(config) {
    return client.post('/api/mercuries/documents/update/status', config);
}
