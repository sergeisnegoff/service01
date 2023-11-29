export const COMPANY_USER_ROLES = {
    ruleAll: {
        title: 'Полный доступ',
        id: 'ruleAll'
    },
    ruleInvoice: {
        title: 'Накладные',
        id: 'ruleInvoice'
    },
    ruleMercuri: {
        title: 'Меркурий',
        id: 'ruleMercuri'
    },
    ruleCreateUser: {
        title: 'Создание пользователей',
        id: 'ruleCreateUser'
    },
    ruleProduct: {
        title: 'Номенклатура',
        id: 'ruleProduct'
    }
};

export const COMPANY_USER_ROLES_KEYS = {
    ruleAll: false,
    ruleInvoice: false,
    ruleMercuri: false,
    ruleCreateUser: false,
    ruleProduct: false
};

export const COMPANY_USER_ROLES_BUYER = [
    COMPANY_USER_ROLES.ruleInvoice.id,
    COMPANY_USER_ROLES.ruleMercuri.id,
    COMPANY_USER_ROLES.ruleCreateUser.id
];

export const COMPANY_USER_ROLES_SUPPLIER = [
    COMPANY_USER_ROLES.ruleInvoice.id,
    COMPANY_USER_ROLES.ruleCreateUser.id,
    COMPANY_USER_ROLES.ruleProduct.id
];
