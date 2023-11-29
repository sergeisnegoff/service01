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
                    title: 'Статус приемки',
                    col: 2
                },
                {
                    title: 'Дата приемки',
                    col: 1
                },
                {
                    title: 'Сумма',
                    col: 1
                },
                {
                    title: 'Название покупателя',
                    col: 2
                },
                {
                    title: 'Сообщение поставщику:',
                    col: 3
                }
            ];

            const items = [{
                fields: [
                    invoice.acceptanceStatus?.title,
                    invoice.acceptanceAt ? formatDate(invoice.acceptanceAt, 'DD.MM.YYYY') : '',
                    invoice.totalPriceWithVat,
                    invoice.buyer?.title,
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
                    title: 'Сумма',
                    col: 1
                },
                {
                    title: 'Принятая сумма',
                    col: 1
                },
                {
                    title: 'Цена с НДС',
                    col: 1
                },
                {
                    title: 'Кол. поставщика',
                    col: 1
                },
                {
                    title: 'Ед.изм. поставщика',
                    col: 1
                },
                {
                    title: 'Номенклатура поставщика',
                    col: 2
                },
                {
                    title: 'Номенклатура покупателя',
                    col: 1
                },
                {
                    title: 'Кол. покупателя',
                    col: 1
                },
                {
                    title: 'Ед.изм покупателя',
                    col: 1
                },
                {
                    title: 'Коэф. покупателя',
                    col: 1
                }
            ];

            const items = this.invoice.data.invoiceProducts.map((item, index) => ({
                fields: [
                    index + 1,
                    item.totalPriceWithVat.toFixed(2),
                    item.comparisonProduct?.totalPriceWithVat
                        ? item.comparisonProduct?.totalPriceWithVat.toFixed(2)
                        : '',
                    item.priceWithVat.toFixed(2),
                    item.quantity,
                    item.unit?.title,
                    item.product?.nomenclature,
                    item.comparisonProduct?.product?.nomenclature,
                    item.comparisonProduct?.acceptQuantity || '',
                    item.comparisonProduct?.unit?.title,
                    item.comparisonProduct?.comparisonRate
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
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Накладные',
                link: { name: 'invoice' }
            }
        ];
    }
};
</script>
