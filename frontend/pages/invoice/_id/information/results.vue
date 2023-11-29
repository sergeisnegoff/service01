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
                            <li>
                                <NuxtLink :to="{ name: 'invoice-id-matching', params: { id: $route.params.id } }">
                                    Сопоставление
                                </NuxtLink>
                            </li>
                            <li class="active">
                                <NuxtLink :to="{ name: 'invoice-id-information', params: { id: $route.params.id } }">
                                    Основные данные
                                </NuxtLink>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
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
import { formatNumberWriting } from '@/helpers/formatNumber';

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
                    title: 'Склад',
                    col: 2
                },
                {
                    title: 'Комментарий',
                    col: 3
                },
                {
                    title: 'Сообщение поставщику:',
                    col: 2
                }
            ];

            const items = [{
                fields: [
                    invoice.dischargeStatus?.title,
                    invoice.acceptanceAt ? formatDate(invoice.acceptanceAt, 'DD.MM.YYYY') : '',
                    invoice.counterparty?.title,
                    invoice.warehouse?.title,
                    invoice.comment,
                    invoice.messageSupplier
                ]
            }];

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
            },
            {
                title: 'Приемка'
            }
        ];
    }
};
</script>
