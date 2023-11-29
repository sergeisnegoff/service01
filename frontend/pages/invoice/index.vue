<template>
    <section class="box__suppliers__page">
        <div class="container">
            <div class="row">
                <div class="col-5">
                    <h1>Накладные</h1>
                </div>
                <div class="col-7">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div
                            v-if="$auth.user.isSupplier"
                            class="btn btn__icons"
                        >
                            <button @click="$router.push({ name: 'invoice-add' })">
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-add.svg') })` }"></span>
                                Создать накладную
                            </button>
                        </div>
                        <div class="btn btn__icons">
                            <button @click="onColumnsFilterOpen">
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-columns.svg') })` }"></span>
                                Колонки
                            </button>
                        </div>
                        <div class="btn btn__icons">
                            <button @click="showProductsFilter">
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-filter.svg') })` }"></span>
                                Фильтрация
                            </button>
                        </div>
                        <div
                            v-if="$auth.user.isBuyer"
                            class="btn btn__icons"
                        >
                            <button @click="fetchInvoicesFromEdo">
                                Получить накладные из ЭДО
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <ListController
                ref="listController"
                v-model="invoices"
                class="table__content"
                :fetch-api="fetchInvoices"
                :fetch-options="fetchOptions"
                :field-to-watch="formData"
                :pre-fetch="false"
            >
                <template v-slot="{ items }">
                    <BaseTable
                        v-if="items"
                        class="table-invoice"
                        :headers="table.headers"
                        :items="table.items"
                        :sorting="true"
                        :sort-direction="formData.sortDirection"
                        @sort="onSort"
                        @item-click="toInvoice"
                    />
                </template>
            </ListController>
        </div>
    </section>
</template>
<script>
import { isObject } from 'lodash';
import { fetchInvoices, fetchInvoiceStatuses, fetchInvoiceColumns, sendInvoiceColumns } from '@/api/invoices';
import { fetchSuppliersList } from '@/api/supplier';
import { fetchBuyerShops, fetchBuyersList } from '@/api/buyer';
import { importElectronicDocumentManagement } from '@/api/electronicDocumentManagement';
import formatNumber from '@/helpers/formatNumber';
import formatDate from '@/helpers/formatDate';
import processError from '@/helpers/processError';
import acceptanceStatuses from '@/constants/acceptanceStatuses';

export default {
    name: 'ProductsPage',
    fetch() {
        return Promise.all([
            this.fetchShops(),
            this.fetchInvoiceStatuses(),
            this.$auth.user.isBuyer
                ? this.fetchSuppliersList()
                : this.fetchBuyersList(),
            this.fetchColumns()
        ]).then(async() => {
            await this.fillFormData();
            this.setTableColumns();
            await this.$refs.listController.fetchData();
        });
    },
    data() {
        return {
            formData: {},
            invoices: {
                data: null,
                loading: false,
                cancel: null
            },
            shops: {
                data: [],
                loading: true,
                cancel: null
            },
            acceptanceStatuses: {
                data: [],
                loading: true,
                cancel: null
            },
            suppliers: {
                data: [],
                loading: true,
                cancel: null
            },
            buyers: {
                data: [],
                loading: true,
                cancel: null
            },
            columns: {
                data: [],
                loading: true
            }
        };
    },
    computed: {
        fetchOptions() {
            const {
                dateFrom,
                dateTo,
                priceFrom,
                priceTo,
                shopId,
                companyId,
                sortField,
                sortDirection,
                search,
                acceptanceStatusId
            } = this.formData;

            return {
                dateFrom,
                dateTo,
                search,
                priceFrom,
                priceTo,
                shopId: shopId?.id || null,
                companyId: companyId?.id || '',
                acceptanceStatusId: acceptanceStatusId?.id || '',
                sortField,
                sortDirection,
                limit: 8
            };
        },
        tableColumns() {
            let columns = {
                id: {
                    key: 'number',
                    title: '№ накладной поставщика',
                    rootClass: 'col-2',
                    isVisible: false
                },
                createdAt: {
                    title: 'Дата',
                    rootClass: 'col-2',
                    isVisible: false
                },
                supplier: {
                    title: 'Поставщик',
                    rootClass: 'col-1',
                    isVisible: false
                },
                shop: {
                    title: 'Торговая точка',
                    rootClass: 'col-2',
                    isVisible: false
                },
                totalPriceWithVat: {
                    title: 'Сумма',
                    rootClass: 'col-2',
                    isVisible: false
                },
                acceptanceStatus: {
                    title: 'Статус приемки',
                    rootClass: 'col-2',
                    isVisible: false
                },
                acceptedTotalPrice: {
                    title: 'Принятая сумма',
                    rootClass: 'col-2',
                    isVisible: false
                }
            };

            return columns;
        },
        table() {
            let headers = [
                {
                    title: '№',
                    field: 'id',
                    col: 2
                },
                {
                    title: 'Дата',
                    field: 'createdAt',
                    col: 1
                },
                {
                    title: 'Поставщик',
                    field: 'supplier',
                    col: 2
                },
                {
                    title: 'Торговая точка',
                    field: 'shop',
                    col: 2
                },
                {
                    title: 'Сумма',
                    field: 'totalPriceWithVat',
                    col: 2
                },
                {
                    title: 'Статус приемки',
                    field: 'acceptanceStatus',
                    col: 2
                },
                {
                    title: 'Принятая сумма',
                    field: 'acceptedTotalPrice',
                    col: 1
                }
            ];

            headers = headers.map(item => item.notSorting ? item : ({ ...item, sortBy: this.formData.sortField === item.field }));
            headers.forEach(item => {
                const isVisible = this.tableColumns[item.field]?.isVisible;

                if (!('isHidden' in item)) item.isHidden = !isVisible;
            });

            const items = this.invoices.data.items.map(item => ({
                raw: item,
                theme: [
                    acceptanceStatuses.ACCEPT_PARTIALLY.code, acceptanceStatuses.NOT_ACCEPTED.code, acceptanceStatuses.CANCELED.code
                ].includes(item.acceptanceStatus?.code) ? 'warning' : '',
                fields: [
                    item.number,
                    item.date,
                    item.supplier?.title || '',
                    item.shop?.title || '',
                    item.totalPriceWithVat,
                    item.acceptanceStatus?.title || '',
                    item.acceptedTotalPrice || ''
                ]
            }));

            return {
                headers,
                items
            };
        }
    },
    methods: {
        onSort(field) {
            if (this.formData.sortField === field) {
                this.formData.sortDirection = this.formData.sortDirection === 'DESC' ? 'ASC' : 'DESC';
            } else {
                this.formData.sortField = field;
                this.formData.sortDirection = 'DESC';
            }
        },
        toInvoice(item) {
            const name = this.$auth.user.isBuyer ? 'invoice-id-acceptance' : 'invoice-id';

            this.$router.push({ name, params: { id: item.id } });
        },
        setTableColumns() {
            const columns = this.tableColumns;

            if (!this.columns.data.length) {
                Object.values(columns).forEach(item => item.isVisible = true);

                return columns;
            }

            this.columns.data.forEach(item => {
                if (columns[item]) {
                    columns[item].isVisible = true;
                }
            });

            return columns;
        },
        resetFormData() {
            this.formData = {
                sortField: 'createdAt',
                sortDirection: 'DESC',
                search: '',
                dateFrom: '',
                dateTo: '',
                companyId: '',
                shopId: '',
                acceptanceStatusId: '',
                priceFrom: '',
                priceTo: ''
            };
        },
        async fillFormData() {
            const query = this.$route.query;

            this.formData = {
                search: query.search || '',
                sortField: query.sortField || 'createdAt',
                sortDirection: query.sortDirection || 'DESC',
                dateFrom: query.dateFrom || '',
                dateTo: query.dateTo || '',
                acceptanceStatusId: this.acceptanceStatuses.data.find(item => +item.id === +query.acceptanceStatusId) || '',
                companyId: this.$auth.user.isBuyer
                    ? this.suppliers.data.find(item => +item.id === +query.companyId) || ''
                    : this.buyers.data.find(item => +item.id === +query.companyId) || '',
                shopId: this.shops.data?.find(item => +item.id === +query.shopId) || '',
                priceFrom: query.priceFrom || '',
                priceTo: query.priceTo || ''
            };
        },
        showProductsFilter() {
            this.$layer.open('ProductsFilter', {
                formData: this.formData,
                shops: this.shops,
                suppliers: this.suppliers,
                buyers: this.buyers,
                acceptanceStatuses: this.acceptanceStatuses,
                onSubmit: (data) => this.formData = data,
                onReset: () => this.resetFormData()
            });
        },
        checkIfShopInShops() {
            if (!this.shops.data.find(item => item.id === this.formData.shopId?.id) && this.formData.shopId) {
                this.formData.shopId = '';
            }
        },
        async onColumnsFilterOpen() {
            const activeColumns = await this.$layer.open('ColumnsFilter', {
                columns: this.tableColumns
            });
            if (!activeColumns || !isObject(activeColumns && activeColumns.columns)) return;

            Object.keys(activeColumns.columns).forEach(key => {
                const internalCol = this.tableColumns[key];
                if (!internalCol || !activeColumns.columns) return;

                internalCol.isVisible = activeColumns.columns[key];
            });

            await sendInvoiceColumns({
                columns: Object.entries(activeColumns.columns)
                    .map(([key, value]) => value && key)
                    .filter(Boolean)
            });
            await this.$refs.listController.fetchData();
        },
        async fetchInvoicesFromEdo() {
            try {
                await importElectronicDocumentManagement();
                await this.$refs.listController.fetchData();

                this.$layer.alert({
                    title: 'Данные успешно получены'
                });
            } catch (e) {
                processError(e);
            }

        },
        async fetchInvoices(...config) {
            let data = await fetchInvoices(...config);

            data.items = data.items.map(item => ({
                ...item,
                date: formatDate(item.createdAt, 'DD.MM.YYYY'),
                totalPriceWithVat: formatNumber(item.totalPriceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                acceptedTotalPrice: item.acceptedTotalPrice ? formatNumber(item.acceptedTotalPrice, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ''
            }));

            return data;
        },
        async fetchShops() {
            if (this.shops.cancel) {
                this.shops.cancel();
            }

            const data = await fetchBuyerShops({
                cancelToken: new this.$axios.CancelToken((cancel) => {
                    this.shops.cancel = cancel;
                }),
                params: {
                    companyId: this.$auth.user?.company?.id
                }
            });

            this.shops = { data, loading: false, cancel: null };
        },
        async fetchInvoiceStatuses() {
            if (this.acceptanceStatuses.cancel) {
                this.acceptanceStatuses.cancel();
            }

            const data = await fetchInvoiceStatuses({
                cancelToken: new this.$axios.CancelToken((cancel) => {
                    this.acceptanceStatuses.cancel = cancel;
                }),
                params: {  code: 'acceptance' }
            });

            this.acceptanceStatuses = { data, loading: false, cancel: null };
        },
        async fetchSuppliersList() {
            this.suppliers.loading = true;

            if (this.suppliers.cancel) {
                this.suppliers.cancel();
            }

            const data = await fetchSuppliersList({
                cancelToken: new this.$axios.CancelToken((cancel) => {
                    this.suppliers.cancel = cancel;
                })
            });

            this.suppliers = { data, loading: false, cancel: null };
        },
        async fetchBuyersList() {
            this.buyers.loading = true;

            if (this.buyers.cancel) {
                this.buyers.cancel();
            }

            const data = await fetchBuyersList({
                cancelToken: new this.$axios.CancelToken((cancel) => {
                    this.buyers.cancel = cancel;
                })
            });

            this.buyers = { data, loading: false, cancel: null };
        },
        async fetchColumns() {
            const data = await fetchInvoiceColumns();

            this.columns = { data: data.columns, loading: false };
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Накладные'
            }
        ];
    }
};
</script>
