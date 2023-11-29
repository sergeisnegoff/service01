<template>
    <section class="box__buyers__page invoice-page">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 :class="invoice.loading && '-preloader'">
                        <template v-if="!invoice.loading">
                            Накладная №{{ invoice.data.number }} от {{ formatDate(invoice.data.createdAt, 'DD.MM.YYYY') }} (Поставщик: {{ invoice.data.supplier ? invoice.data.supplier.title : '' }})
                        </template>
                    </h1>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="box__tabs">
                        <ul>
                            <li>
                                <NuxtLink :to="{ name: 'invoice-id-acceptance', params: { id: $route.params.id } }">
                                    Приемка
                                </NuxtLink>
                            </li>
                            <li class="active">
                                <NuxtLink :to="{ name: 'invoice-id-matching', params: { id: $route.params.id } }">
                                    Сопоставление
                                </NuxtLink>
                            </li>
                            <li>
                                <NuxtLink :to="{ name: 'invoice-id-information', params: { id: $route.params.id } }">
                                    Основные данные
                                </NuxtLink>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <BaseTable
                v-if="invoice.data && table.items.length"
                :headers="table.headers"
                :items="table.items"
                :fixed-columns="true"
            />
            <BaseTable
                v-if="invoice.data"
                :headers="tableResults.headers"
                :items="tableResults.items"
            />
        </div>
    </section>
</template>
<script>
import { fetchInvoice } from '@/api/invoices';
import formatDate from '@/helpers/formatDate';
import formatNumber from '@/helpers/formatNumber';

export default {
    name: 'ProductsPage',
    fetch() {
        return Promise.all([
            this.fetchInvoice()
        ]);
    },
    data() {
        return {
            invoice: {
                data: null,
                loading: true
            }
        };
    },
    computed: {
        tableResults() {
            const invoice = this.invoice.data;

            let headers = [
                {
                    title: 'Статус документа',
                    col: 2
                },
                {
                    title: 'Дата приемки',
                    col: 1
                },
                {
                    title: 'Контрагент из учетной записи',
                    col: 2
                },
                {
                    title: 'Комментарий',
                    col: 4
                },
                {
                    title: 'Сообщение поставщику:',
                    col: 3
                }
            ];

            const items = [{
                fields: [
                    invoice.dischargeStatus?.title,
                    invoice.acceptanceAt ? formatDate(invoice.acceptanceAt, 'DD.MM.YYYY') : '',
                    invoice.counterparty?.title,
                    invoice.comment,
                    invoice.messageSupplier
                ]
            }];

            return {
                headers,
                items
            };
        },
        table() {
            let headers = [
                {
                    title: '№',
                    col: 1
                },
                {
                    title: 'Наименование',
                    col: 2
                },
                {
                    title: 'Ед.изм.',
                    col: 1
                },
                {
                    title: 'Наименование покупателя',
                    col: 1
                },
                {
                    title: 'Ед.изм.',
                    col: 1
                },
                {
                    title: 'Коэф.',
                    col: 1
                },
                {
                    title: 'Принято факт',
                    col: 1
                },
                {
                    title: 'Кол-во к учету',
                    col: 1
                },
                {
                    title: 'Цена',
                    col: 1
                },
                {
                    title: 'Сумма',
                    col: 1
                },
                {
                    title: 'Сумма факт',
                    col: 1
                }
            ];

            const items = this.invoice.data.invoiceProducts.map((item, index) => ({
                fields: [
                    index + 1,
                    item.product?.nomenclature,
                    item.unit?.title,
                    item.comparisonProduct?.product?.nomenclature,
                    item.comparisonProduct?.unit?.title || item.product.unit?.title,
                    item.comparisonProduct?.comparisonRate,
                    formatNumber(item.comparisonProduct?.quantity || item.quantity, { maximumFractionDigits: 2 }),
                    formatNumber(item.comparisonProduct?.quantityFact, { maximumFractionDigits: 2 }),
                    formatNumber(item.priceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    formatNumber(item.totalPriceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    this.getTotalProductPrice(item)
                ]
            }));

            return {
                headers,
                items
            };
        }
    },
    methods: {
        formatDate,
        async fetchInvoice() {
            let data = await fetchInvoice(this.$route.params.id, {
                params: {
                    withComparison: true
                }
            });

            this.invoice = { data, loading: false };
        },
        getTotalProductPrice(item) {
            const quantity = item.comparisonProduct?.quantity || item.quantity;

            return formatNumber(quantity * item.priceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Накладные',
                link: { name: 'invoice' }
            },
            {
                title: 'Приемка'
            }
        ];
    }
};
</script>
