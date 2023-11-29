export default function({ $axios, route, store }) {
    const request = route.name === 'all'
        ? $axios.$get('/api/pages', {
            params: {
                path: route.path
            }
        })
        : $axios.$get('/api/pages/' + route.name);

    return request
        .then(page => store.dispatch('page/update', page))
        .catch(() => store.dispatch('page/reset'))
        .then(() => {
            if (process.server) {
                store.commit('page/fix');
            }
        });
}
