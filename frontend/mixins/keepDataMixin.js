const objectStorage = window.sessionStorage || {
    items: new Map(),
    getItem(key) {
        return this.items.get(key);
    },
    setItem(key, value) {
        return this.items.set(key, value);
    }
};

const sessionStorage = window.sessionStorage || objectStorage;
const localStorage = window.localStorage || sessionStorage;

const storages = {
    object: objectStorage,
    local: localStorage,
    session: sessionStorage
};

function getStorage(type) {
    return storages[type] || objectStorage;
}

function key(propertyName, options) {
    return [options.name || this._name, propertyName]
        .concat(options.keys.map(k => k + this[k]))
        .filter(v => v)
        .join('.');
}

function write(storage, key, data) {
    storage.setItem(key, JSON.stringify(data));
}

function read(storage, key) {
    const data = storage.getItem(key);
    if (!data) {
        return null;
    }

    return JSON.parse(data);
}

export default function keepDataMixin(propertyName, options) {
    options = {
        storage: 'session', // session | object | local
        name: '',
        keys: [],
        ...options || {}
    };

    const storage = getStorage(options.storage);

    return {
        watch: {
            [propertyName]: {
                handler(data) {
                    write(
                        storage,
                        key.call(this, propertyName, options),
                        data
                    );
                },
                deep: true
            }
        },
        created() {
            const data = read(
                storage,
                key.call(this, propertyName, options)
            );

            if (data) {
                this[propertyName] = data;
            }
        }
    };
}
