import client from '@/helpers/api';

export function fetchBuyersList(config) {
    return client.get('/api/buyers', config);
}
export function fetchBuyerShops(config) {
    return client.get('/api/buyers/shops', config);
}
export function fetchBuyerSelfShops(config) {
    return client.get('/api/buyers/self/organizations/shops', config);
}
export function removeBuyerOrganization(organizationId, config) {
    return client.delete(`/api/buyers/self/organizations/${ organizationId }`, config);
}
export function removeBuyerOrganizationShop(shopId, config) {
    return client.delete(`/api/buyers/self/organizations/shops/${ shopId }`, config);
}
export function changeBuyerOrganizationShop(shopId, config) {
    return client.put(`/api/buyers/self/organizations/shops/${ shopId }`, config);
}
export function importOrganizationsShopsFromSmart(config) {
    return client.post('/api/buyers/self/organizations/shops/smart/import', config);
}
export function approveOrganizationsShopsFromSmart(config) {
    return client.post('/api/buyers/self/organizations/shops/smart/approve', config);
}
export function addBuyerOrganizationShop(config) {
    return client.post('/api/buyers/self/organizations/shops', config);
}
export function changeBuyerOrganization(organizationId, config) {
    return client.put(`/api/buyers/self/organizations/${ organizationId }`, config);
}
export function addAlternativeTitleToShop(shopId, config) {
    return client.put(`/api/buyers/organizations/shops/${ shopId }/alternativeTitle `, config);
}
export function fetchSupplierOrganizations(config) {
    return client.get('/api/buyers/organizations', config);
}
export function fetchOrganizationShop(organizationId, config) {
    return client.get(`/api/buyers/self/organizations/${ organizationId }/shops`, config);
}

