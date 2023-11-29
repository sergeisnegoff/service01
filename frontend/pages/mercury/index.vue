<template>
    <section class="box__buyers__page">
        <div class="container">
            <div class="row align-center">
                <div class="col-6">
                    <h1>Меркурий</h1>
                </div>
                <div class="col-3">
                    <div class="box__search">
                        <form @submit.prevent="onSearch">
                            <div class="box__input">
                                <input v-model="list.query" type="text" placeholder="Поиск по таблице">
                            </div>
                            <div class="btn__search">
                                <button></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-3">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn btn__icons">
                            <button @click="onColumnsFilterOpen">
                                <span :style="{backgroundImage: `url(${ require('@/assets/img/icon/table-columns.svg') })`}"></span>Колонки
                            </button>
                        </div>
                        <div
                            class="btn btn__icons"
                            :class="{
                                '-preloader -preloader_sm': filtrate.loading
                            }"
                        >
                            <button @click="onFilterOpen">
                                <span :style="{backgroundImage: `url(${ require('@/assets/img/icon/table-filter.svg') })`}"></span>Фильтрация
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12 margin-15">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn">
                            <button
                                :disabled="!list.selectedItems.length"
                                @click="$layer.open('MercuryFeedbackLayer', { documentIds: [...list.selectedItems] })"
                            >
                                Сообщить о проблеме
                            </button>
                        </div>
                        <div class="btn">
                            <button :disabled="!repaySelectedActionActive" @click="onRepaySelected">
                                Погасить выбранные
                            </button>
                        </div>
                        <div class="btn">
                            <button :disabled="repayOutstanding.loading" :class="{ '-preloader -preloader_sm': repayOutstanding.loading}" @click="onRepayOutstanding">
                                Погасить непогашенные ВСД
                            </button>
                        </div>
                        <div class="btn btn__icons">
                            <button
                                :class="{
                                    '-preloader -preloader_sm': updateList.loading
                                }"
                                :disabled="updateList.disabled"
                                @click="onUpdateDocuments"
                            >
                                <span :style="{backgroundImage: `url(${ require('@/assets/img/icon/table-refresh.svg') })`}"></span>Обновить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <MercuryNavigation />
                </div>
            </div>
            <div class="row">
                <div class="col-12 pb-10">
                    <div
                        class="box__content__table table-invoice"
                        :class="{
                            '-preloader pb-10': list.loading
                        }"
                    >
                        <div class="table__content-title box__table-sorting">
                            <div class="row">
                                <div class="col-1">
                                    <div class="box__checkbox">
                                        <div class="wrapper-checkbox">
                                            <label>
                                                <input v-model="isAllSelected" type="checkbox">
                                                <span>
                                                    <span class="box__checkbox-icon"></span>
                                                    <span class="box__checkbox-text"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div v-for="col in tableHeaderCols" :key="col.key" :class="col.rootClass">
                                    <BaseTooltip
                                        class="w-full"
                                        placement="top-start"
                                        arrow="true"
                                    >
                                        <template #trigger>
                                            <div class="flex">
                                                <h3 v-html="col.title"></h3>
                                                <div
                                                    class="btn__sorting"
                                                    :class="{
                                                        'btn__sorting-ascending': (list.sort === SORT_ASC && (!list.sortBy || (list.sortBy === col.key))),
                                                        'btn__sorting-descending': true,
                                                    }"
                                                >
                                                    <button @click="sortBy(col.key)">
                                                        <span></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        <div v-html="col.title"></div>
                                    </BaseTooltip>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="list.items.length"
                            class="table__content"
                        >
                            <div
                                v-for="item in list.items"
                                :key="item.uuid"
                                class="box__table__item"
                            >
                                <div class="box__item" data-table>
                                    <div class="row">
                                        <div class="col-1">
                                            <div class="box__checkbox">
                                                <div class="wrapper-checkbox">
                                                    <label>
                                                        <input v-model="list.selectedItems" type="checkbox" :value="item.uuid">
                                                        <span>
                                                            <span class="box__checkbox-icon"></span>
                                                            <span class="box__checkbox-text"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="tableColumns.sender.isVisible" class="col-1">
                                            {{ item.sender }}
                                        </div>
                                        <div v-if="tableColumns.recipient.isVisible" class="col-1">
                                            {{ item.recipient }}
                                        </div>
                                        <div v-if="tableColumns.productTitle.isVisible" class="col-2">
                                            {{ item.productTitle }}
                                        </div>
                                        <div v-if="tableColumns.status.isVisible" class="col-1">
                                            {{ item.status }}
                                        </div>
                                        <div v-if="tableColumns.issueDate.isVisible" class="col-2">
                                            {{ item.issueDateFormatted }}
                                        </div>
                                        <div v-if="tableColumns.uuid.isVisible" class="col-2">
                                            {{ item.uuid }}
                                        </div>
                                        <div v-if="tableColumns.waybillNumber.isVisible" class="col-1">
                                            {{ item.waybillNumber }}
                                        </div>
                                        <div v-if="tableColumns.productQuantity.isVisible" class="col-1">
                                            {{ item.productQuantity }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            Ничего не найдено
                        </div>
                        <div class="table__content-bottom">
                            <div class="row">
                                <div class="col-12">
                                    <Pagination
                                        :page="Number(list.pagination.page)"
                                        :pages="Number(list.pagination.pages)"
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
import { fetchVSD, fetchVSDFilter, repayVSD, updateVSDList, updateVSDStatus, fetchMercuryColumns, sendMercuryColumns } from '@/api/mercury';
import { isObject } from 'lodash';
import { normalizeVSDList, normalizeVSDFilterAttributes } from '@/normalizers/vsd';

const SORT_ASC = 'ASC';
const SORT_DESC = 'DESC';
const filtrateInitialAttrs = {
    sender: null,
    status: null,
    issueDate: null
};

export default {
    name: 'Mercury',
    fetch() {
        return Promise.all([
            this.fetchUpdateVSDStatus(),
            this.fetchColumns()
        ]).then(() => {
            this.setTableColumns();
            this.fetchData();
        });
    },
    data() {
        return {
            SORT_ASC,
            SORT_DESC,
            list: {
                items: [],
                pagination: {
                    page: 1,
                    pages: 0
                },
                query: '',
                sortBy: 'issueDate',
                sort: SORT_DESC,
                limit: 7,
                loading: false,
                cancel: null,
                selectedItems: []
            },
            updateList: {
                loading: false,
                disabled: true
            },
            repayOutstanding: {
                loading: false
            },
            columns: {
                data: [],
                loading: true
            },
            filtrate: {
                loading: false,
                attributesLoaded: false,
                attributes: {
                    sender: [],
                    status: []
                },
                formData: filtrateInitialAttrs,
                errors: {
                    sender: '',
                    status: '',
                    issueDate: ''
                }
            },
            tableColumns: {
                sender: {
                    key: 'sender',
                    title: 'Отправитель',
                    rootClass: 'col-1',
                    isVisible: true
                },
                recipient: {
                    key: 'recipient',
                    title: 'Получатель',
                    rootClass: 'col-1',
                    isVisible: true
                },
                productTitle: {
                    key: 'productTitle',
                    title: 'Название товара',
                    rootClass: 'col-2',
                    isVisible: true
                },
                status: {
                    key: 'status',
                    title: 'Статус документа',
                    rootClass: 'col-1',
                    isVisible: true
                },
                issueDate: {
                    key: 'issueDate',
                    title: 'Дата оформления ВСД',
                    rootClass: 'col-2',
                    isVisible: true
                },
                uuid: {
                    key: 'uuid',
                    title: 'Внешний код ВСД',
                    rootClass: 'col-2',
                    isVisible: true
                },
                waybillNumber: {
                    key: 'waybillNumber',
                    title: 'Номер накладной',
                    rootClass: 'col-1',
                    isVisible: true
                },
                productQuantity: {
                    key: 'productQuantity',
                    title: 'Кол-во. товара',
                    rootClass: 'col-1',
                    isVisible: true
                }
            }
        };
    },
    computed: {
        isAnySelected() {
            return this.list.selectedItems.length > 0;
        },
        isAllSelected: {
            get() {
                return this.list.items.length === this.list.selectedItems.length;
            },
            set(isAllChecked) {
                this.toggleAll(isAllChecked);
            }
        },
        repaySelectedActionActive() {
            return this.isAnySelected && !this.list.loading;
        },
        tableHeaderCols() {
            const cols = this.tableColumns;

            return Object.keys(cols)
                .map(colKey => {
                    return cols[colKey];
                })
                .filter(x => x.isVisible);
        },
        filtrateNormalizedData() {
            return {
                sender: this.filtrate.formData.sender?.id,
                status: this.filtrate.formData.status?.id,
                issueDate: this.filtrate.formData.issueDate
            };
        }
    },
    methods: {
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
        async fetchData() {
            const page = this.list.pagination.page || 1;
            const { limit } = this.list;
            const { query } = this.list;
            const { sort } = this.list;
            const { sortBy } = this.list;

            try {
                return fetchVSD({
                    cancelToken: new this.$axios.CancelToken((cancel) => {
                        this.list.cancel = cancel;
                        this.list.loading = true;
                    }),
                    params: {
                        page,
                        limit,
                        query,
                        sort,
                        sortBy,
                        ...this.filtrateNormalizedData
                    },
                    progress: false
                })
                    .then(response => {
                        this.list.items = normalizeVSDList(response.items);
                        this.list.pagination = response.pagination;
                    })
                    .finally(() => {
                        this.list.loading = false;
                    });
            } catch (e) {
                console.log('Unable to fetch company mercury', e);
            }
        },
        async fetchFilterAttributes() {
            this.filtrate.loading = true;
            try {
                return fetchVSDFilter()
                    .then(response => {
                        this.filtrate.attributes = normalizeVSDFilterAttributes(response);
                        this.filtrate.attributesLoaded = true;
                    })
                    .finally(() => {
                        this.filtrate.loading = false;
                    });
            } catch (e) {
                console.log('Unable to fetch VDS filter attributed', e);
            }
        },
        async fetchColumns() {
            const data = await fetchMercuryColumns();

            this.columns = { data: data.columns, loading: false };
        },
        async repaySelectedVSD() {
            this.list.loading = true;

            try {
                return repayVSD({
                    documentIds: this.list.selectedItems
                })
                    .then(() => {
                        this.$layer.alert({
                            title: 'Запрос отправлен',
                            message: 'Ваш запрос обрабатывается. Результаты будут получены в течение 15 минут'
                        });
                    })
                    .finally(() => {
                        this.list.loading = false;
                    });
            } catch (e) {
                this.list.loading = false;
                console.log('Unable to repay', e);
            }
        },
        async repayOutstandingVSD() {
            this.repayOutstanding.loading = true;
            try {
                return repayVSD({
                    unredeemed: true
                })
                    .then(() => {
                        this.$layer.alert({
                            title: 'Запрос отправлен',
                            message: 'Ваш запрос обрабатывается. Результаты будут получены в течение 15 минут'
                        });
                    })
                    .finally(() => {
                        this.repayOutstanding.loading = false;
                    });
            } catch (e) {
                this.repayOutstanding.loading = false;
                console.log('Unable to repay', e);
            }
        },
        async doUpdateVsdList() {
            this.updateList.loading = true;
            try {
                return updateVSDList().
                    then(() => {
                        this.$layer.alert({
                            title: 'Запрос отправлен',
                            message: 'Ваш запрос обрабатывается. Результаты будут получены в течение 15 минут'
                        });
                        this.fetchUpdateVSDStatus();
                    })
                    .finally(() => {
                        this.updateList.loading = false;
                    });
            } catch (e) {
                this.updateList.loading = false;
                console.log('Unable to update VSD list', e);
            }
        },
        onPaginate(page) {
            this.list.pagination.page = page;
            this.list.selectedItems = [];
            this.fetchData();
        },
        async onRepaySelected() {
            if (!this.list.selectedItems.length) return;

            try {
                await this.repaySelectedVSD();
                this.list.selectedItems = [];
                await this.fetchData();
            } catch (e) {
                console.log('Unable to repay', e);
            }
        },
        async onRepayOutstanding() {
            try {
                await this.repayOutstandingVSD();
                this.list.selectedItems = [];
                await this.fetchData();
            } catch (e) {
                console.log('Unable to repay', e);
            }
        },
        fetchUpdateVSDStatus() {
            try {
                this.updateList.loading = true;

                return updateVSDStatus()
                    .then((response) => {
                        this.updateList.disabled = !response.state;
                    })
                    .finally(() => {
                        this.updateList.loading = false;
                    });
            } catch (e) {
                console.log('Unable to fetch update VSD status', e);
            }
        },
        onUpdateDocuments() {
            if (this.updateList.disabled) {
                return false;
            }
            this.doUpdateVsdList();
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

            await sendMercuryColumns({
                columns: Object.entries(activeColumns.columns)
                    .map(([key, value]) => value && key)
                    .filter(Boolean)
            });
            await this.fetchData();
        },
        onSearch() {
            this.list.pagination.page = 1;
            this.list.sortBy = '';
            this.list.sort = SORT_DESC;
            this.filtrate.formData = filtrateInitialAttrs;
            this.fetchData();
        },
        sortBy(sortTag) {
            if (!sortTag) return;

            if (this.list.sortBy === sortTag) {
                this.list.sort = this.list.sort === SORT_ASC ? SORT_DESC : SORT_ASC;
            } else {
                this.list.sortBy = sortTag;
            }

            this.fetchData();
        },
        toggleAll(isAllChecked) {
            if (isAllChecked) {
                this.list.selectedItems = this.list.items.map(x => x.uuid);

                return;
            }

            this.list.selectedItems = [];
        },
        async onFilterOpen() {
            if (!this.filtrate.attributesLoaded) {
                await this.fetchFilterAttributes();
            }
            const filtrateResult = await this.$layer.open('MercuryFilter', this.filtrate);
            if (!filtrateResult) return;
            this.list.pagination.page = 1;
            this.fetchData();
        }
    },
    breadcrumbs() {
        const items = [];

        items.push(
            {
                title: 'Меркурий',
                link: {
                    name: 'mercury'
                }
            },
            {
                title: 'Общие'
            }
        );

        return items;
    }
};
</script>
