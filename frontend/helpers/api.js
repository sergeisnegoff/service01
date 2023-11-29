class NuxtAxiosClient {
    client = null

    get() {
        return this.client.$get.apply(null, arguments);
    }

    post() {
        return this.client.$post.apply(null, arguments);
    }

    put() {
        return this.client.$put.apply(null, arguments);
    }

    patch() {
        return this.client.$patch.apply(null, arguments);
    }

    delete() {
        return this.client.$delete.apply(null, arguments);
    }

    request() {
        return this.client.$request.apply(null, arguments);
    }

    head() {
        return this.client.$head.apply(null, arguments);
    }

    options() {
        return this.client.$options.apply(null, arguments);
    }
}

const api = new NuxtAxiosClient();
export default api;

export function setRequestClient(client) {
    api.client = client;
}
