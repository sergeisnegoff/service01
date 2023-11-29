export default function({ app, store }) {
    app.router.afterEach(function() {
        if (process.client) {
            store.commit('page/fix');
        }
    });
}
