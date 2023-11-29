<template>
    <section class="box__suppliers__page">
        <div v-if="!company.data.loading" class="container">
            <div class="row">
                <div class="col-6">
                    <h1>{{ company.data.title }}</h1>
                </div>
                <div class="col-6">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn">
                            <button @click="$router.push({ name: 'supplier-id-export', params: { id: $route.params.id } })">
                                Экспорт товаров
                            </button>
                        </div>
                        <div class="btn btn__icon-favorite btn-right">
                            <button @click="toggleFavorite(!company.data.isFavorite, company.data.id)">
                                <span :style="{backgroundImage: `url(${ require('@/assets/img/icon/to-favorites.svg') })`}"></span>
                                {{ company.data.isFavorite ? 'Удалить с избранного' : 'В избранное' }}
                            </button>
                        </div>
                        <div
                            class="btn"
                            @click="doSendRequestJob"
                        >
                            <button :disabled="company.data.isJobRequest">
                                Начать работу
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <TabLinks :items="getNavigation" />
                </div>
            </div>
            <div class="row content-aboutcompany">
                <div class="col-3">
                    <div class="aside__category">
                        <div class="row">
                            <div class="col-12">
                                <h4>Каталог</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div
                                    class="aside__category-catalog"
                                    :class="{
                                        '-preloader': categories.loading
                                    }"
                                >
                                    <ul v-if="categories.data">
                                        <Collapse
                                            v-for="category in categories.data"
                                            :key="category.id"
                                            class-name="li"
                                            :class="{
                                                '-preloader': category.isLoading,
                                                'aside__category-add': formData.categories.includes(category.id) || category.children.find(item => formData.categories.includes(item.id))
                                            }"
                                        >
                                            <template v-slot="{ isOpened, doToggle }">
                                                <template>
                                                    <a
                                                        class="aside__category-title"
                                                        href="#"
                                                        @click="filterByCategory(category.id)"
                                                    >
                                                        {{ category.title }}
                                                    </a>
                                                    <span
                                                        v-if="category.children.length"
                                                        class="aside__category-toggler"
                                                        :class="{ 'is-open': isOpened }"
                                                        @click="doToggle"
                                                    ></span>
                                                </template>
                                                <ul v-if="isOpened">
                                                    <li
                                                        v-for="child in category.children"
                                                        :key="child.id"
                                                        :class="{
                                                            'aside__category-active': false // ToDo: поправить активный пункт
                                                        }"
                                                    >
                                                        <div>
                                                            <div
                                                                class="aside__category-edit"
                                                            >
                                                                <a
                                                                    href="#"
                                                                    :class="formData.categories.includes(child.id) && 'is-active'"
                                                                    @click="filterByCategory(child.id)"
                                                                >
                                                                    {{ child.title }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </template>
                                        </Collapse>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-9">
                    <div class="box__content__table">
                        <ListController
                            ref="listController"
                            v-model="products"
                            class="table__content"
                            :fetch-api="fetchProducts"
                            :fetch-options="fetchOptions"
                            :field-to-watch="formData"
                        >
                            <template v-slot="{ items }">
                                <div class="table__content-title">
                                    <div class="row">
                                        <div class="col-4">
                                            <h3>Номенклатура</h3>
                                        </div>
                                        <div class="col-1">
                                            <h3>Ед. изм</h3>
                                        </div>
                                        <div class="col-2">
                                            <h3>Артикул</h3>
                                        </div>
                                        <div class="col-3">
                                            <h3>Штрихкод</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="table__content">
                                    <div
                                        v-for="(item, index) in items"
                                        :key="index"
                                        class="box__table__item"
                                    >
                                        <div class="box__item" data-table>
                                            <div class="row">
                                                <div class="col-4">
                                                    {{ item.title }}
                                                </div>
                                                <div class="col-1">
                                                    {{ item.unit && item.unit.title }}
                                                </div>
                                                <div class="col-2">
                                                    {{ item.article }}
                                                </div>
                                                <div class="col-3">
                                                    {{ item.barcode }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </ListController>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { fetchCompanyDetail } from '@/api/company';
import { fetchProducts } from '@/api/product';
import { fetchProductAllCategories } from '@/api/product';
import { sendJobRequest } from '@/api/supplier';
import { normalizeProductsForTable } from '@/normalizers/products';
import companyFavorite from '@/mixins/companyFavorite';
import processError from '@/helpers/processError';

export default {
    name: 'SupplierGoodsPage',
    mixins: [
        companyFavorite
    ],
    fetch() {
        return Promise.all([
            this.fetchData(),
            this.fetchCategories()
        ]);
    },
    data() {
        return {
            formData: {
                categories: []
            },
            products: {
                data: null,
                loading: false,
                cancel: null
            },
            categories: {
                data: null,
                loading: false
            },
            company: {
                data: {},
                loading: false,
                cancel: null
            }
        };
    },
    computed: {
        fetchOptions() {
            return {
                companyId: this.$route.params.id,
                categoriesId: this.formData.categories,
                limit: 8
            };
        },
        getNavigation() {
            if (this.$auth.user.isBuyer) {
                return [
                    {
                        id: 'tab1',
                        title: 'О организации',
                        link: this.$route.name === 'supplier-id' ? null : {
                            name: 'supplier-id',
                            params: this.$route.params.id
                        },
                        active: this.$route.name === 'supplier-id'
                    },
                    {
                        id: 1,
                        title: 'Товары',
                        link: this.$route.name === 'supplier-id-goods' ? null : {
                            name: 'supplier-id-goods',
                            params: this.$route.params.id
                        },
                        active: this.$route.name === 'supplier-id-goods'
                    }
                ];
            } else if (this.$auth.user.isModerator) {
                return [{
                    id: 'tab1',
                    title: 'О организации',
                    link: this.$route.name === 'supplier-id' ? null : {
                        name: 'buyer-id',
                        params: this.$route.params.id
                    },
                    active: this.$route.name === 'supplier-id'
                }];
            } else {
                return [
                    {
                        id: 'tab1',
                        title: 'О организации',
                        link: this.$route.name === 'buyer-id' ? null : {
                            name: 'buyer-id',
                            params: this.$route.params.id
                        },
                        active: this.$route.name === 'buyer-id'
                    },
                    {
                        id: 1,
                        title: 'Торговые точки',
                        link: this.$route.name === 'buyer-id-organizations' ? null : {
                            name: 'buyer-id-organizations',
                            params: this.$route.params.id
                        },
                        active: this.$route.name === 'buyer-id-organizations'
                    }
                ];
            }
        }
    },
    methods: {
        async fetchData() {
            return fetchCompanyDetail(this.$route.params.id, {
                data: {
                    cancelToken: new this.$axios.CancelToken((cancel) => {
                        this.company.cancel = cancel;
                        this.company.loading = true;
                    })
                }
            })
                .then(response => {
                    this.company.data = response;
                })
                .finally(() => {
                    this.company.loading = false;
                    this.company.cancel = false;
                });
        },
        async fetchProducts(...config) {
            const data = await fetchProducts(...config);

            return normalizeProductsForTable(data);
        },
        async fetchCategories() {
            let data = await fetchProductAllCategories({
                params: {
                    companyId: this.$route.params.id
                }
            });

            data = data.map(item => ({
                ...item,
                isOpen: false
            }));

            this.categories = { data, loading: false };
        },
        filterByCategory(id) {
            this.formData.categories = [id];
            this.fetchProducts();
        },
        async doSendRequestJob() {
            if (this.company.data.isJobRequest) return;

            const text = await this.$layer.open('RequestJobLayer');
            if (!text) return;

            try {
                await sendJobRequest(this.company.data.id, {
                    text
                });
                this.fetchData();
            } catch (e) {
                processError(e);
            }
        }
    },
    breadcrumbs({ store }) {
        const items = [];

        if (!store.$auth.user.isModerator) items.push({
            title: 'Поставщики',
            link: {
                name: 'supplier'
            }
        });

        items.push({
            title: 'Товары'
        });

        return items;
    }
};
</script>
