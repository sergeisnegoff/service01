<template>
    <section class="box__buyers__page">
        <div class="container">
            <div v-if="companyData.title" class="row">
                <div class="col-12">
                    <h1>{{ companyData.title }}</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <TabLinks :items="getNavigation" />
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div
                        class="box__content__table"
                        :class="{
                            '-preloader': list.loading
                        }"
                    >
                        <div class="table__content-title">
                            <div class="row">
                                <div class="col-2">
                                    <h3>Название</h3>
                                </div>
                                <div class="col-3">
                                    <h3>Адрес</h3>
                                </div>
                                <div class="col-2">
                                    <h3>Координаты</h3>
                                </div>
                                <template v-if="$auth.user.isSupplier">
                                    <div class="col-5">
                                        <h3>Альтернативное название</h3>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="col-3">
                                        <h3>GLN</h3>
                                    </div>
                                    <div class="col-2">
                                        <h3>Внешний код ДИАДОК</h3>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="table__content">
                            <template v-if="list.items && list.items.length">
                                <div
                                    v-for="shop in list.items"
                                    :key="shop.id"
                                    class="box__table__item"
                                    :class="{
                                        '-preloader': shop.isLoading
                                    }"
                                    @keyup.esc="doCancelItem(shop)"
                                >
                                    <div
                                        class="box__item"
                                        data-table
                                    >
                                        <div
                                            v-if="shop.isEdit"
                                            class="box__item-edit"
                                        >
                                            <div class="row">
                                                <div class="col-2">
                                                    {{ shop.title }}
                                                </div>
                                                <div class="col-3">
                                                    {{ shop.addressTitle }}
                                                </div>
                                                <div class="col-2">
                                                    {{ shop.coordinates }}
                                                </div>
                                                <div class="col-4">
                                                    <InputField
                                                        v-model="shop.alternativeTitle"
                                                        placeholder="Альтернативное описание"
                                                        :error="shop.errors.alternativeTitle"
                                                    />
                                                </div>
                                                <div class="btn-accept">
                                                    <button
                                                        type="submit"
                                                        @click="doSaveShop({ shop })"
                                                    >
                                                        <span></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <div class="row">
                                                <div class="col-2">
                                                    {{ shop.title }}
                                                </div>
                                                <div class="col-3">
                                                    {{ shop.addressTitle }}
                                                </div>
                                                <div class="col-2">
                                                    {{ shop.coordinates }}
                                                </div>
                                                <template v-if="$auth.user.isSupplier">
                                                    <div class="col-5">
                                                        {{ shop.alternativeTitle }}
                                                    </div>
                                                    <div class="btn-edit">
                                                        <button @click="doEditItem(shop)">
                                                            <span></span>
                                                        </button>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <div class="col-3">
                                                        {{ shop.docrobotExternalCode }}
                                                    </div>
                                                    <div class="col-2">
                                                        {{ shop.diadocExternalCode }}
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-else-if="!list.loading">
                                Ничего не найдено
                            </div>
                        </div>
                        <div v-if="list.pagination && list.pagination.pages > 1" class="table__content-bottom">
                            <div class="row">
                                <div class="col-12">
                                    <Pagination
                                        :pages="Number(list.pagination.pages)"
                                        :page="Number(list.pagination.page)"
                                        @paginate="onPaginate"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { fetchBuyerShops, addAlternativeTitleToShop } from '@/api/buyer';
import { normalizeOrganizationShops } from '@/normalizers/organization';
import { isObject, isNull } from 'lodash';
import { fetchCompanyDetail } from '@/api/company';

export default {
    name: 'BuyerOrganizationsPage',
    fetch() {
        return Promise.all([
            this.fetchData(),
            this.fetchList()
        ]);
    },
    data() {
        return {
            company: {
                data: {},
                loading: false,
                cancel: null
            },
            list: {
                items: [],
                pagination: {
                    page: 1,
                    pages: 0
                },
                limit: 10,
                loading: false
            },
            formData: {
            }
        };
    },
    computed: {
        getNavigation() {
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
        },
        companyData() {
            return this.company?.data || {};
        }
    },
    methods: {
        async fetchData() {
            this.company.loading = true;

            return fetchCompanyDetail(this.$route.params.id)
                .then(response => {
                    this.company.data = response;
                })
                .finally(() => {
                    this.company.loading = false;
                    this.company.cancel = false;
                });
        },
        async fetchList() {
            const page = this.list.pagination.page || 1;
            const { limit } = this.list;
            this.list.loading = true;

            return fetchBuyerShops({
                params: {
                    page,
                    limit,
                    ...this.formData,
                    companyId: this.$route.params.id
                },
                progress: false
            })
                .then(response => {
                    this.list.items = normalizeOrganizationShops(response.items);
                    this.list.pagination = response.pagination;
                })
                .finally(() => {
                    this.list.loading = false;
                });

        },
        async doSaveShop({ shop } = {}) {
            if (!isObject(shop) || isNull(shop)) return;
            const { alternativeTitle } = shop;
            shop.isLoading = true;

            try {
                await addAlternativeTitleToShop(shop.id, {
                    alternativeTitle
                });
                this.doCancelItem(shop);
                shop.isLoading = false;
                await this.fetchList();
            } catch (e) {
                console.log('Unable to add alternative title to shop', e);
            }
            shop.isLoading = false;
        },
        onPaginate(page) {
            this.list.pagination.page = page;
            this.fetchList();
        },
        doEditItem(item) {
            if (!isObject(item) || isNull(item)) return;

            item.isEdit = true;
        },
        doCancelItem(item) {
            if (!isObject(item) || isNull(item)) return;

            item.isEdit = false;
        }
    }
};
</script>
