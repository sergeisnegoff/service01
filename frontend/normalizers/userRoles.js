import { isObject, isNull, isArray } from 'lodash';
import { COMPANY_USER_ROLES } from '@/constants/company';

export function normalizeUserRole(item) {
    if (!isObject(item) || isNull(item)) return {};
    const shopTitlesArray = (isArray(item.shops) ? item.shops : []).map(shop => shop && shop.title);
    const shopNames = shopTitlesArray.join(', ');
    const organizationArray = (isArray(item.organizations) ? item.organizations : []).map(organization => organization && organization.title);
    const organizationNames = organizationArray.join(', ');
    const rolesObject = (isArray(item.rules) ? item.rules : [])
        .map(roleId => COMPANY_USER_ROLES[roleId])
        .filter(x => x);

    return {
        ...item,
        shopNames,
        rolesObject,
        organizationNames
    };
}

export function normalizeUserRoles(list) {
    if (!isArray(list)) return [];

    return list.map(item => normalizeUserRole(item));
}
