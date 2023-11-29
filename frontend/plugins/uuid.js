let counter = 0;

/**
 * @param {String} prefix
 */
export const generateUuid = (prefix) => (typeof prefix === 'string' && prefix) + ++counter;

export default () => {
    counter = 0;
};
