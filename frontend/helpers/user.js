import { isObject } from 'lodash';
import { COMPANY_USER_ROLES } from '@/constants/company';

export const ruleNeedPages = {
    supplier: {
        [COMPANY_USER_ROLES.ruleInvoice.id]: /^invoice*/gui,
        [COMPANY_USER_ROLES.ruleProduct.id]: /^products*/gui,
        [COMPANY_USER_ROLES.ruleCreateUser.id]: /^company-users*/gui
    },
    buyer: {
        [COMPANY_USER_ROLES.ruleInvoice.id]: /^invoice*/gui,
        [COMPANY_USER_ROLES.ruleMercuri.id]: /^mercury*/gui,
        [COMPANY_USER_ROLES.ruleCreateUser.id]: /^company-users*/gui
    }
};

export function isUserModificationGranted(user) {
    return isGranted(user, COMPANY_USER_ROLES.ruleCreateUser.id);
}

export function isUserPageGranted(user, routeName) {
    let key;

    if (!user) return false;
    if (!user.isSubordinate) return true;

    if (user.isSupplier) {
        key = 'supplier';
    } else if (user.isBuyer) {
        key = 'buyer';
    } else if (user.isModerator) {
        key = 'moderator';
    }

    const roles = ruleNeedPages[key];

    return Object.keys(roles).every(roleKey => {
        let rolePattern = roles[roleKey];

        return rolePattern.test(routeName) ? user.rules?.rules?.find(item => item === roleKey) : true;
    });
}

export function isGranted(user, rule) {
    if (!isObject(user)) return false;

    const isSubordinate = user.isSubordinate;
    const isCreateUserGranted = user.rules?.rules?.includes(rule);

    return !isSubordinate || isCreateUserGranted;
}
