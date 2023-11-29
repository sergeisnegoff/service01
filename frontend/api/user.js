import client from '@/helpers/api';

export function fetchUser(config) {
    return client.get('/api/users/self', config);
}
export function updateUser(data, config) {
    return client.post('/api/users/self/company', data, config);
}
export function findUser(config) {
    return client.get('/api/users/find', config);
}

export function userRegister(config) {
    return client.post('/api/users', config);
}
export function userAuthorize(config) {
    return client.post('/api/users/tokens', config);
}
export function userAuthorizeAsModerator(config) {
    return client.post('/api/users/tokens/moderator', config);
}

/**
 *
 * @param {Object} config
 * @param {string} config.step - Шаг (one, two, three)
 * @param {string} config.phone - Номер телефона
 * @param {string} config.code - Код подтверждения телефона
 * @param {string} config.password - Новый пароль
 * @returns {Promise}
 */
export function userRemindPassword(config) {
    return client.post('/api/users/restorePassword', config);
}
