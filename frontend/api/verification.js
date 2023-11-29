import client from '@/helpers/api';

export function requestVerification(scope, config) {
    return client.post(`/api/verificationCode/${ scope }`, config);
}

export function verifyCode(scope, config) {
    return client.put(`/api/verificationCode/${ scope }`, config);
}

