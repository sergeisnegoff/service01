export default function({ store, route }) {
    window.onNuxtReady(function(app) {
        app.$nuxt.$on('routeChanged', function(to) {
            store.commit('breadcrumbs/reset');

            to.matched.forEach(x => {
                if (x.components.default.options.breadcrumbs) {
                    const items = x.components.default.options.breadcrumbs.call(x.components.default.options.data && x.components.default.options.data() || {}, { store, route: to });
                    if (items && items.length) {
                        store.commit('breadcrumbs/addItems', items);
                    }
                }
            });
        });
    });
}
