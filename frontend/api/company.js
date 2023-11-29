import client from '@/helpers/api';

export function fetchCompany(config) {
    return client.get('/api/companies/self', config);
}
export function addCompany(config) {
    return client.post('/api/companies/self/list', config);
}
export function fetchCompanyDetail(companyId, config) {
    return client.get(`/api/companies/${ companyId }`, config);
}
export function saveCompany({ data }) {
    return client.post('/api/companies/self', data);
}
export function fetchCompanyList(config) {
    return client.get('/api/companies/self/list', config);
}
export function selectCompany(companyId, config) {
    return client.put(`/api/companies/self/choose/${ companyId }`, config);
}
export function changeCompanyVisibility(companyId, config) {
    return client.put(`/api/companies/self/${ companyId }/visible`, config);
}
export function changeCompanyOwners(companyId, config) {
    return client.put(`/api/companies/self/${ companyId }/changeOwners`, config);
}
export function companyFavoriteAdd(config) {
    return client.post('/api/companies/self/favorites', config);
}
export function companyJoinInn(config) {
    return client.post('/api/companies/self/list/join', config);
}
export function companyFavoriteRemove(data, config) {
    return client.delete('/api/companies/self/favorites', data, config);
}
export function companyAddComment(companyId, config) {
    return client.post(`/api/companies/${ companyId }/comment`, config);
}
export function fetchCompanyUsers(config) {
    return client.get('/api/companies/self/users', config);
}
export function addCompanyUser(config) {
    return client.post('/api/companies/self/users', config);
}
export function saveCompanyUser(userId, data, config) {
    return client.post(`/api/companies/self/users/${ userId }`, data, config);
}
export function removeCompanyUsers(userId, config) {
    return client.delete(`/api/companies/self/users/${ userId }`, config);
}
export function fetchCompanyUserById(userId, config) {
    return client.get(`/api/companies/self/users/${ userId }`, config);
}
export function setCompanyUserActivity(userId, config) {
    return client.put(`/api/companies/self/users/${ userId }/active`, config);
}
export function fetchCompanyUserRole(userId, config) {
    return client.get(`/api/companies/self/users/${ userId }/rules`, config);
}
export function addCompanyUserRole(userId, config) {
    return client.post(`/api/companies/self/users/${ userId }/rules`, config);
}
export function editCompanyUserRole(roleId, config) {
    return client.put(`/api/companies/self/users/rules/${ roleId }`, config);
}
export function deleteCompanyUserRole(roleId, config) {
    return client.delete(`/api/companies/self/users/rules/${ roleId }`, config);
}
