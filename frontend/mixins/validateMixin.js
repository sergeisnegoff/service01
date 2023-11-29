import { isEqual } from 'lodash';

/**
 *
 * @param {String} dataProp имя поля с данными для валидации
 * @param {String} errorProp поле для ошибок
 * @param {Object} validations варианты валидации
 * @param {Object <String>} [validations.required] обязательные поля
 */
export default function validateMixin(dataProp, errorProp, validations) {
    if (!validations) {
        throw new TypeError('validateMixin: Нужно указать проверки');
    }

    const dataName = dataProp.charAt(0).toUpperCase() + dataProp.slice(1);
    const cacheDataName = dataProp + 'Validate';

    return {
        watch: {
            /**
             * Следим за изменением данных чтобы убрать ошибку с поля
             */
            [cacheDataName]: {
                handler(formData, oldData) {
                    const errors = Object.assign({}, this[errorProp]);

                    Object.keys(formData).filter((name) => !isEqual(formData[name], oldData[name]))
                        .forEach((name) => {
                            if (Array.isArray(formData[name])) {
                                formData[name].forEach((item, index) => {
                                    if (!oldData[name]) return;

                                    const oldItem = oldData[name][index];

                                    if (!oldItem) return;

                                    Object.keys(item).forEach(key => {
                                        let el = item[key];
                                        let elOld = oldItem[key];

                                        if (el !== elOld && errors[name] && errors[name][index]) {
                                            delete errors[name][index][key];
                                            if (!Object.keys(errors[name][index]).length) delete errors[name][index];
                                            if (!Object.keys(errors[name]).length) delete errors[name];
                                        }
                                    });
                                });
                            } else delete errors[name];
                        });

                    this[errorProp] = errors;
                }
            }
        },

        methods: {
            ['validate' + dataName]() {
                const errors = Object.assign({}, this[errorProp]);

                if (validations.required) {
                    Object.keys(validations.required).forEach((name) => {
                        let prop = this[dataProp][name];
                        let validation = validations.required[name];
                        let isFilled;

                        if (Array.isArray(validation) && prop) {
                            Object.keys(validation[0]).forEach(key => {
                                prop.forEach((item, index) => {
                                    if (!item[key]) {
                                        if (typeof errors[name] !== 'object') errors[name] = {};
                                        if (!errors[name][index]) errors[name][index] = {};
                                        errors[name][index] = {
                                            ...errors[name][index],
                                            [key]: validation[0][key]
                                        };
                                    }
                                });
                            });

                            return;
                        } else if (Array.isArray(prop)) isFilled = prop.length;
                        else isFilled = prop;

                        if (!isFilled) {
                            errors[name] = validations.required[name];
                        }
                    });
                }

                this[errorProp] = errors;
            }
        },

        computed: {
            [cacheDataName]() {
                return Object.assign({}, JSON.parse(JSON.stringify(this[dataProp])));
            }
        }
    };
}
