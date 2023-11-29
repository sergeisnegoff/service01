export const utilNormalizedVariant = {
    computed: {
        normalizedVariant() {
            let result = {};
    
            for (let key in this) {
                if (key.endsWith('Variant') && key !== 'normalizedVariant') {
                    for (let variantKey in this[key]) {
                        const variantOption = this[key][variantKey];
        
                        if (result[variantKey]) result[variantKey].push(variantOption);
                        else result[variantKey] = [variantOption];
                    }
                }
            }
    
            for (let key in result) result[key] = result[key].join(' ');
    
            return result;
        }
    }
};

/**
 *
 * @param {String} variant название prop с названием варианта
 * @param {String} variants название prop с объектом вариантов
 * @param {Object} settings установки
 * @param {Object <String>} [settings.default] название варианта по умолчанию
 * @param {Object <Object>} [settings.variants] дефолтные варианты
 */
export function createUtil(variant, variants, settings = {}) {
    const dataVariant = variant + 'Variant';
    const dataVariants = variant + 'Variants';
    
    return {
        props: {
            [variant]: { type: String, default: settings.default || '' },
            [variants]: { type: Object, default: () => ({}) }
        },
        data: () => ({
            [dataVariants]: { ...(settings.variants || {}) }
        }),
        computed: {
            [dataVariant]() {
                const collectedVariants = Object.assign(this[variants], this[dataVariants]);
                const collectedNormalizedVariants = getNormalizedVariants.call(this, collectedVariants);
                
                return collectedNormalizedVariants[this[variant]] || {};
            }
        }
    };
}

function getNormalizedVariants(variants) {
    let result = {};
    
    for (let key in variants) result[key] = getNormalizedVariant.call(this, variants[key]);
    
    return result;
}

function getNormalizedVariant(variant) {
    let result = {};
    
    for (let key in variant) result[key] = getNormalizedVariantOption.call(this, variant[key]);
    
    return result;
}

function getNormalizedVariantOption(option) {
    let result = '';
    let optionType = typeof option;
    
    if (optionType === 'string') result = option;
    else if (optionType === 'function') result = option.call(this);
    else if (optionType === 'object') result = optionsToString.call(this, option);
    
    return result;
}

function optionsToString(option) {
    return Object.values(option).map(item => typeof item === 'function' ? item.call(this) : item)
        .filter(Boolean)
        .join(' ');
}
