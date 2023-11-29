<template>
    <section class="box__buyers__page invoice-page">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 :class="invoice.loading && '-preloader'">
                        <template v-if="!invoice.loading">
                            Накладная №{{ normalizedInvoice.number }} от {{ formatDate(normalizedInvoice.createdAt, 'DD.MM.YYYY') }} (Поставщик: {{ normalizedInvoice.supplier ? normalizedInvoice.supplier.title : '' }})
                        </template>
                    </h1>
                </div>
                <div
                    v-if="invoice.data && !normalizedInvoice.acceptanceStatus"
                    class="col-12 mt-30"
                >
                    <div class="wraapper__button-nowrap content__justify-start">
                        <div
                            v-if="isAcceptFull"
                            key="accept-full"
                            class="btn btn-green btn__icons"
                        >
                            <button
                                :disabled="sending"
                                @click.prevent="send"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                Принять полностью
                            </button>
                        </div>
                        <div
                            v-else
                            key="accept-part"
                            class="btn btn__icons"
                        >
                            <button
                                :disabled="sending"
                                @click.prevent="send"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-failure.svg') })` }"></span>
                                Принять с расхождениями
                            </button>
                        </div>
                        <div class="btn btn-red btn__icons">
                            <button
                                :disabled="sending"
                                @click.prevent="send({ cancel: true, close: true })"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-cancel.svg') })` }"></span>
                                Аннулировать и закрыть
                            </button>
                        </div>
                        <div class="btn btn-green btn__icons">
                            <button
                                :disabled="sending"
                                @click.prevent="send({ save: true })"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                Сохранить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="row"
                :class="!formData.products.length && 'mb-4'"
            >
                <div class="col-12">
                    <div class="box__tabs">
                        <ul>
                            <li class="active">
                                <NuxtLink :to="{ name: 'invoice-id-acceptance', params: { id: $route.params.id } }">
                                    Приемка
                                </NuxtLink>
                            </li>
                            <li>
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
            <div
                v-if="formData.products.length"
                class="row"
            >
                <div class="col-12">
                    <div class="box__content__table table-invoice">
                        <div class="table__content-title box__table-sorting">
                            <div class="row">
                                <div class="col-1">
                                    <h3>№</h3>
                                </div>
                                <div class="col-3">
                                    <h3>Наименование</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Ед.изм.</h3>
                                </div>
                                <div class="col-2">
                                    <h3>Кол-во</h3>
                                </div>
                                <div class="col-2">
                                    <h3>Принято</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Цена</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Сумма</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Сумма факт</h3>
                                </div>
                            </div>
                        </div>
                        <PerfectScrollbar
                            ref="scroll-content"
                            :options="{
                                wheelSpeed: 0.8,
                                wheelPropagation: true,
                                minScrollbarLength: 20,
                                useBothWheelAxes: false,
                                suppressScrollX: true
                            }"
                            tag="div"
                            class="table__content"
                            style="position: relative;height: calc(100vh - 480px); min-height: 445px"
                        >
                            <div
                                v-for="(item, index) in normalizedInvoice.invoiceProducts"
                                :key="index"
                                class="box__table__item"
                            >
                                <div class="box__item" data-table>
                                    <div class="row">
                                        <div class="col-1">
                                            {{ index + 1 }}
                                        </div>
                                        <div class="col-3">
                                            {{ item.product && item.product.nomenclature }}
                                        </div>
                                        <div class="col-1">
                                            {{ item.unit && item.unit.title }}
                                        </div>
                                        <div class="col-2">
                                            {{ item.quantity }}
                                        </div>
                                        <div class="col-2">
                                            <InputField
                                                v-model="formData.products[index].quantity"
                                                mask="number"
                                                placeholder=""
                                            />
                                        </div>
                                        <div class="col-1">
                                            {{ formatNumber(item.priceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                        </div>
                                        <div class="col-1">
                                            {{ formatNumber(item.totalPriceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                        </div>
                                        <div class="col-1">
                                            {{ getTotalProductPrice(item, index) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </PerfectScrollbar>
                        <div class="box__table__item">
                            <div class="box__item" data-table>
                                <div class="row">
                                    <div class="col-1"></div>
                                    <div class="col-9">
                                        Итого
                                    </div>
                                    <div class="col-1">
                                        {{ formatNumber(invoice.data.totalPriceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                    </div>
                                    <div class="col-1">
                                        {{ totalPrice }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <BaseTable
                v-if="invoice.data && (normalizedInvoice.acceptanceAt || normalizedInvoice.dischargeStatus)"
                :headers="tableFixed.headers"
                :items="tableFixed.items"
            />
        </div>
    </section>
</template>
<script>
import { fetchInvoice, sendInvoiceAccept } from '@/api/invoices';
import formatDate from '@/helpers/formatDate';
import processError from '@/helpers/processError';
import formatNumber from '@/helpers/formatNumber';

export default {
    name: 'ProductsPage',
    async asyncData({ route, redirect }) {
        let data = await fetchInvoice(route.params.id, {
            params: {
                withComparison: true
            }
        });

        if (data.acceptanceStatus) {
            redirect({ name: 'invoice-id-acceptance-results', params: { id: route.params.id } });
        }

        return {
            invoice: {
                loading: false,
                data
            }
        };
    },
    data() {
        return {
            formData: {
                products: []
            },
            formDataIsFilled: false,
            invoice: {
                data: null,
                loading: false
            },
            sending: false
        };
    },
    computed: {
        normalizedInvoice() {
            return {
                ...this.invoice.data
            };
        },
        tableFixed() {
            const invoice = this.normalizedInvoice;

            let headers = [
                {
                    title: ' Статус выгрузки',
                    col: 2
                },
                {
                    title: 'Дата приемки',
                    col: 2
                }
            ];

            const items = [{
                fields: [
                    invoice.dischargeStatus?.title,
                    invoice.acceptanceAt ? formatDate(invoice.acceptanceAt, 'DD.MM.YYYY') : ''
                ]
            }];

            return {
                headers,
                items
            };
        },
        isAcceptFull() {
            return this.normalizedInvoice.invoiceProducts.every((item, index) => {
                const coef = +item.comparisonProduct?.comparisonRate || 1;

                return item.quantity === (+this.formData.products[index].quantity * coef);
            });
        },
        totalPrice() {
            const price = this.normalizedInvoice.invoiceProducts.reduce((acc, item, index) => {
                return acc + (this.formData.products[index].quantity * item.priceWithVat);
            }, 0);

            return formatNumber(price, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    },
    created() {
        this.fillFormData();
    },
    methods: {
        formatDate,
        formatNumber,
        async fetchInvoice() {
            let data = await fetchInvoice(this.$route.params.id, {
                params: {
                    withComparison: true
                }
            });

            this.invoice = { data, loading: false };
        },
        fillFormData() {
            const invoice = this.normalizedInvoice;

            this.formData = {
                products: invoice.invoiceProducts.map((item) => ({
                    quantity: item.comparisonProduct?.acceptQuantity || item.quantity
                }))
            };

            this.formDataIsFilled = true;
        },
        getTotalProductPrice(item, index) {
            return formatNumber(this.formData.products[index].quantity * item.priceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        async send(options = {}) {
            this.sending = true;

            const formData = { ...this.formData };

            try {
                await sendInvoiceAccept(this.$route.params.id, {
                    ...formData,
                    products: this.formData.products.map((item, index) => ({
                        quantity: item.quantity,
                        invoiceProductId: this.normalizedInvoice.invoiceProducts[index]?.id
                    })),
                    cancel: options.cancel,
                    save: options.save
                });
                await this.fetchInvoice();
                this.fillFormData();

                await this.$layer.alert({
                    message: 'Успешно'
                });

                if (options.close) {
                    this.$router.push({ name: 'invoice' });

                    return;
                }

                if (this.normalizedInvoice.acceptanceStatus) {
                    this.$router.push({ name: 'invoice-id-acceptance-results', params: { id: this.$route.params.id } });
                }
            } catch (e) {
                console.log(e);
                processError(e);
            }

            this.sending = false;
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
