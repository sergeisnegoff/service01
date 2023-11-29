export default function({ beforeNuxtRender, store, app, route }) {
    beforeNuxtRender(({ Components }) => {
        Components.forEach(component => {
            if (typeof component.options.breadcrumbs !== 'function') {
                return;
            }

            store.commit('breadcrumbs/addItems', component.options.breadcrumbs.call(app.context.ssrContext.asyncData && app.context.ssrContext.asyncData[component.cid] || {}, { store, route }));
        });
    });
}
