import client from '@/helpers/api';

export function fetchReportSubjects(config) {
    return client.get('/api/forms/subject/reports', config);
}
export function sendForm(formId, config) {
    return client.post(`/api/forms/${ formId }`, config);
}
