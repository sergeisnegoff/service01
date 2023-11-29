<template>
    <component
        :is="componentName"
        :class="{
            '-preloader': value.loading
        }"
    >
        <div v-if="value.data && (value.data.pagination ? value.data.items.length : items.data.length)">
            <slot v-bind="value.data">
            </slot>
        </div>
        <div class="mb-4" v-else-if="showTextIfEmpty">
            Ничего не найдено
        </div>
        <slot name="additional">
        </slot>
        <Pagination
            v-if="value.data && value.data.pagination"
            class="mt-5"
            :page="value.data.pagination.page"
            :pages="value.data.pagination.pages"
            @paginate="onPaginate"
        />
    </component>
</template>

<script>
import { cloneDeep, isEmpty, isEqual } from 'lodash';
import scrollIntoView from '@/helpers/scrollIntoView';

export default {
    props: {
        value: {
            type: Object,
            validate(value) {
                return value && 'loading' in value && 'data' in value && 'cancel' in value;
            }
        },
        showTextIfEmpty: { type: Boolean, default: true },
        componentName: { type: String, default: 'div' },
        fieldToWatch: { type: Object, default: () => ({}) },
        fetchApi: { type: Function, default: () => {} },
        fetchOptions: { type: Object, default: () => ({}) },
        elementToScroll: { type: Object },
        exclude: { type: Array, default: () => [] },
        preFetch: { type: Boolean, default: true }
    },
    async fetch() {
        if (!this.preFetch) return;

        return this.fetchData().then(() => this.$emit('after-prefetch'));
    },
    data() {
        return {
            page: this.$route.query.page || 1
        };
    },
    computed: {
        query() {
            return this.$route.query;
        },
        fieldToWatchCached() {
            return Object.assign({}, cloneDeep(this.fieldToWatch));
        }
    },
    watch: {
        async fieldToWatchCached(newData, oldData) {
            if (isEmpty(oldData) || isEmpty(newData) || isEqual(newData, oldData)) return;

            this.page = 1;

            try {
                let data = await this.getData();

                this.$emit('input', { ...this.value, data });
                await this.$router.push({ query: this.buildUrlQuery(oldData) });
                await this.$nextTick();

                this.scrollToFirstElement();
            } catch (e) {
                console.log(e);
                this.$emit('input', { ...this.value, loading: false });
            }
        },
        query(val) {
            if (val.page && +val.page !== this.page) {
                this.page = +val.page;
                this.fetchData();
            }
        },
        page(val, prevVal) {
            if (prevVal && val !== prevVal) {
                this.$router.push({ query: { ...this.$route.query, page: val } });
            }
        }
    },
    methods: {
        async getData() {
            if (this.value.cancel) {
                this.value.cancel();
            }

            this.$emit('input', { ...this.value, loading: true });
            await this.$nextTick();

            const data = await this.fetchApi({
                cancelToken: new this.$axios.CancelToken((cancel) => {
                    this.$emit('input', { ...this.value, cancel: cancel });
                }),
                params: {
                    page: this.page,
                    limit: 1,
                    ...this.fetchOptions
                }
            });

            this.$emit('input', { ...this.value, loading: false });
            await this.$nextTick();

            return data;
        },
        fetchData() {
            return this.getData()
                .then(data => {
                    this.$emit('input', { ...this.value, data });
                })
                .catch(e => {
                    this.$emit('input', { ...this.value, loading: false });
                    console.log(e);
                });
        },
        scrollToFirstElement() {
            let ref = this.elementToScroll;
            let coords;

            if (Array.isArray(ref)) ref = ref[0];
            if (!ref) return;

            ref = ref.$el || ref;
            coords = ref.getBoundingClientRect();

            if (ref && (coords.y < 0)) {
                scrollIntoView(ref, { y: -40 });
            }
        },
        onPaginate(page) {
            this.page = page;
            this.fetchData();
        },
        buildUrlQuery(oldData) {
            const query = { ...this.query, page: this.page };

            oldData = oldData || {};

            for (let key in this.fieldToWatchCached) {
                const item = this.fieldToWatchCached[key];

                if (this.exclude.includes(key) || isEqual(item, oldData[key])) continue;

                if (Array.isArray(item)) query[key] = item.length ? item : undefined;
                else if (typeof item === 'object' && item !== null) query[key] = item?.id;
                else if (!isEmpty(item)) query[key] = item;
                else delete query[key];
            }

            return query;
        }
    }
};
</script>
