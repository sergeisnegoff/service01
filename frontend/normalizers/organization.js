function fillErrorFields(obj) {
    const errors = {};
    Object.keys(obj).forEach(x => {
        if ((typeof obj[x] === 'object' && obj[x] !== null) && Array.isArray(obj[x])) return;
        errors[x] = '';
    });

    return errors;
}

export function normalizeOrganizationShop(item) {
    if (!item || typeof item !== 'object') return null;

    return {
        ...item,
        addressTitle: item.address,
        docrobotExternalCode: item.docrobotExternalCode,
        diadocExternalCode: item.diadocExternalCode,
        address: {
            title: item.address,
            latitude: item.latitude,
            longitude: item.longitude
        },
        coordinates: ([
            item.latitude,
            item.longitude
        ]
            .filter(x => x)
            .join(', ')),
        alternativeTitle: item.alternativeTitle || '',
        isEdit: false,
        isLoading: false,
        errors: fillErrorFields(item)
    };
}
export function normalizeOrganizationShops(items) {
    return (Array.isArray(items) ? items : []).map(item => normalizeOrganizationShop(item));
}
export function normalizeOrganization(item) {
    if (!item || typeof item !== 'object') return null;

    return {
        ...item,
        shops: normalizeOrganizationShops(item.shops),
        isEdit: false,
        isLoading: false,
        errors: fillErrorFields(item)
    };
}
export function normalizeOrganizations(items) {
    return (Array.isArray(items) ? items : []).map(item => normalizeOrganization(item));
}
