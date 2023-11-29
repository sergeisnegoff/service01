const defaultNumberFormatter = new Intl.NumberFormat('ru-RU', { style: 'decimal', currency: 'RUB', minimumFractionDigits: 0 });

/**
 * Приводит число согласно стандартам
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/NumberFormat
 * @param {number} number
 * @returns {string}
 */
export default function formatNumber(number, options = {}) {
    if (typeof number !== 'number') {
        console.error('formatNumber принимает только числа');

        return number;
    }

    if (options) {
        const { locale = 'ru-RU', ...restOptions } = options;

        return new Intl.NumberFormat(locale, {
            style: 'decimal',
            currency: 'RUB',
            minimumFractionDigits: 0,
            ...restOptions
        }).format(number)
            .replace(/,/gui, '.');
    }

    return defaultNumberFormatter.format(number).replace(/,/gui, '.');
}

export function formatNumberWriting(_number) {
    let number = (_number || 0).toString();

    return Number(number.replace(/,/gui, '.'));
}
