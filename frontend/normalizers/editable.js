import { isObject, isNull } from 'lodash';
export function makeItemEditable(item) {
    if (!isObject(item) || isNull(item)) return null;
    item.isEdit = false;
    item.isLoading = false;
    if (item.children) {
        item.children = makeItemsEditable(item.children);
    }

    return item;
}
export function makeItemsEditable(items) {
    return (Array.isArray(items) ? items : []).map(item => makeItemEditable(item)).filter(x => x);
}
