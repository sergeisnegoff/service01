import Vue from 'vue';

function buildHash(key, locale, context) {
    return JSON.stringify({ key, locale, context });
}

function addLifecycleHook(vm, hook, fn) {
    if (!vm.$options[hook]) {
        vm.$options[hook] = [];
    }
    if (!vm.$options[hook].includes(fn)) {
        vm.$options[hook].push(fn);
    }
}

export default function({ store }, inject) {
    const loadMap = {};

    store.registerModule('storage', {
        namespaced: true,
        state() {
            return {
                items: {}
            };
        },
        mutations: {
            setItem(state, { key, locale, context, data }) {
                const hash = buildHash(key, locale, context);
                Vue.set(state.items, hash, data);
            }
        },
        actions: {
            async loadItem({ state, commit }, { key, locale, context }) {
                const hash = buildHash(key, locale, context);

                if (hash in state.items) {
                    return state.items[hash];
                }

                if (hash in loadMap) {
                    return loadMap[hash];
                }

                return loadMap[hash] = this.$axios
                    .$get('/api/storage/' + key, {
                        params: {
                            locale,
                            context
                        }
                    })
                    .then(data => {
                        commit('setItem', { key, locale, context, data });

                        return data;
                    })
                    .finally(() => delete loadMap[hash]);
            }
        }
    });

    const storage = {
        get(key, locale, context) {
            return store.dispatch('storage/loadItem', { key, locale, context });
        }
    };

    inject('storage', storage);

    const mixin = {
        beforeCreate() {
            if (!this.$options || !this.$options.storageItems) {
                return;
            }

            let items = this.$options.storageItems;

            if (Array.isArray(items)) {
                const _items = {};
                items.forEach(item => _items[item] = item);
                items = _items;
            }

            const properties = Object.keys(items);

            if (!properties.length) {
                return {};
            }

            const computed = {};
            const promises = [];

            properties.forEach(property => {
                const item = items[property];
                let key;
                let defaultValue;

                if (typeof item === 'string') {
                    key = item;

                } else {
                    key = item.key;
                    defaultValue = item.default;
                }

                promises.push(function() {
                    return this.$store.dispatch('storage/loadItem', { key });
                });

                computed[property] = function() {
                    const hash = buildHash(key);

                    if (hash in this.$store.state.storage.items) {
                        return this.$store.state.storage.items[hash];
                    }

                    return defaultValue;
                };
            });

            this.$options.computed = { ...this.$options.computed || {}, ...computed };

            if (process.client) {
                addLifecycleHook(this, 'beforeMount', function() {
                    promises.map(x => x.call(this));
                });

            } else {
                addLifecycleHook(this, 'serverPrefetch', async function() {
                    await Promise.all(promises.map(x => x.call(this)));
                });
            }
        }
    };

    if (!Vue.__nuxt__storage__mixin__) {
        Vue.__nuxt__storage__mixin__ = true;
        Vue.mixin(mixin);
    }
}
