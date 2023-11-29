import formatNumber from './formatNumber';

const defaultPriceFormatter = new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', minimumFractionDigits: 0 });

/**
 * Форматирует число в красивую цену
 * @param {number} number
 * @returns {string}
 */
export default function formatPrice(number, options = {}) {
    if (typeof +number !== 'number') {
        console.error('formatPrice принимает только числа ', number);

        return +number;
    }

    if (options) {
        return formatNumber(+number, { style: 'currency', minimumFractionDigits: +number % 1 ? 3 : 0, ...options });
    }

    return defaultPriceFormatter.format(+number);
}
