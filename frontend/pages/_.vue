<template>
    <div>
    </div>
</template>

<script>
export default {
    validate({ store }) {
        return !!store.state.page.data.id;
    },
    async asyncData({ $axios, store, error }) {
        const data = {};
        data.page = store.state.page.data;

        if (data.page.content && data.page.content.id) {
            data.page.content = await $axios.$get('/api/contents/' + data.page.content.id);
        }

        if (!data.page.content) {
            return error();
        }

        return data;
    },
    data() {
        return {
            page: {}
        };
    }
};
</script>
