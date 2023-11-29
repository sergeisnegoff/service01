import client from '@/helpers/api';

export function fetchRequestsList(config) {
    return client.get('/api/companyVerificationRequests', config);
}

export function changeRequestsStatus(companyId, config) {
    return client.put(`/api/companyVerificationRequests/${ companyId }/status`, config);
}
