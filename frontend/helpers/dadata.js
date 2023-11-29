/**
 *
 * @param {Object} params
 * @param {string} params.token
 * @param {string} params.query
 * @param {string} params.bound
 * @param {Object[]} params.restrictions
 * @param {number} [params.limit=5]
 */
export function buildAddressParams(params) {
    return {
        url: 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address',
        params: {
            query: params.query,
            count: params.limit,
            from_bound: { value: params.bound },
            to_bound: { value: params.bound },
            locations: params.restrictions,
            restrict_value: true
        },
        options: {
            mode: 'cors',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                Authorization: 'Token ' + params.token
            }
        }
    };
}
