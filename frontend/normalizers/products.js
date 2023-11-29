import { makeItemEditable } from './editable';

export function normalizeProductsForTable(data) {
    // ToDo: убрать места, где есть хардкод пагинации. Убрать костыль

    const items = data.items || data;
    const pagination = data.pagination || {
        pages: 1,
        page: 1,
        total: 12
    };

    return {
        pagination,
        items: items.map(item => ({
            id: item.id,
            title: item.nomenclature,
            article: item.article,
            barcode: item.barcode,
            unit: item.unit
        })).map(makeItemEditable)
    };
}
export function normalizeProductsForList(data) {
    return data.map(item => ({
        id: item.id,
        title: item.nomenclature,
        article: item.article
    }));
}
