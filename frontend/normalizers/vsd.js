import { isObject, isArray } from 'lodash';
import formatDate from '@/helpers/formatDate';

/**
 * @typedef {Object} VSD
 * @property {String} id - Идентификатор
 * @property {String} uuid - Номер товарно транспортной накладной
 * @property {String} issueDate - дата оформления
 * @property {String} sender - отправитель
 * @property {String} receiver - получатель
 * @property {String} status - статус
 */

/**
 * @typedef {Object} VSDNormalized
 * @property {String} id - Идентификатор
 * @property {String} uuid - Номер товарно транспортной накладной
 * @property {String} issueDate - дата оформления
 * @property {String} sender - отправитель
 * @property {String} receiver - получатель
 * @property {String} status - статус
 *
 * @property {String} issueDateFormatted - отформатированная дата оформления
 */

/**
 *
 * @param {VSD} item
 * @returns {VSDNormalized}
 */
export function normalizeVSD(item) {
    if (!isObject(item)) return {};

    return {
        ...item,
        uuid: item.uuid,
        issueDateFormatted: item.issueDate ? formatDate(item.issueDate, 'DD.MM.YYYY') : ''
    };
}

/**
 *
 * @param {Array} list
 * @returns {Array}
 */
export function normalizeVSDList(list) {
    return (isArray(list) ? list : []).map(item => normalizeVSD(item));
}

export function normalizeVSDFilterAttributes(filterItems) {
    if (!isObject(filterItems)) return [];
    let attributes = {};

    Object.keys(filterItems).forEach(filterItemKey => {
        const currentFilterItems = filterItems[filterItemKey];
        if (!isObject(currentFilterItems)) return;
        attributes[filterItemKey] = Object.keys(currentFilterItems).map(itemKey => {
            return {
                id: itemKey,
                title: currentFilterItems[itemKey]
            };
        });
    });

    return attributes;
}
