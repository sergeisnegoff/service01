import Url from 'url-parse';
import { isObject, isNull, isArray, isString } from 'lodash';
import formatDate from '@/helpers/formatDate';

export function normalizeNotification(item) {
    if (!isObject(item) || isNull(item)) return {};
    const urlObj = Url(item.link);
    const path = (isString(urlObj.pathname) ? urlObj.pathname : '').endsWith('/') ? urlObj.pathname : `${ urlObj.pathname }/`;

    return {
        ...item,
        title: item.notification?.text,
        createdAt: formatDate(item.createdAt, 'DD.MM.YYYY в HH:mm'),
        path
    };
}
export function normalizeNotifications(list) {
    if (!isArray(list)) return [];

    return list.map(item => normalizeNotification(item));
}
