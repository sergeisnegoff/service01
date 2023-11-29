import client from '@/helpers/api';

export function fetchElectronicDocumentManagementDiadocSettings(config) {
    return client.get('/api/electronicDocumentsManagement/diadoc/settings', config);
}
export function setElectronicDocumentManagementDiadocSettings(config) {
    return client.post('/api/electronicDocumentsManagement/diadoc/settings', config);
}

export function fetchElectronicDocumentManagementDocrobotSettings(config) {
    return client.get('/api/electronicDocumentsManagement/docrobot/settings', config);
}
export function setElectronicDocumentManagementDocrobotSettings(config) {
    return client.post('/api/electronicDocumentsManagement/docrobot/settings', config);
}

export function importElectronicDocumentManagement(config) {
    return client.post('/api/electronicDocumentsManagement/import', config);
}
