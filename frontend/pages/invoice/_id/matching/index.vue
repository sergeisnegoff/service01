<template>
    <section
        class="box__buyers__page invoice-page"
        :class="(invoice.loading || !formDataIsFilled) && '-preloader'"
    >
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 :class="invoice.loading && '-preloader'">
                        <template v-if="!invoice.loading">
                            Накладная №{{ invoice.data.number }} от {{ formatDate(invoice.data.createdAt, 'DD.MM.YYYY') }} (Поставщик: {{ invoice.data.supplier ? invoice.data.supplier.title : '' }})
                        </template>
                    </h1>
                </div>
                <div
                    v-if="showButtons"
                    class="col-12 mt-30"
                >
                    <div
                        class="wraapper__button-nowrap content__justify-start"
                    >
                        <div
                            v-if="isAcceptFull"
                            key="accept-full"
                            class="btn btn-green btn__icons"
                        >
                            <button
                                :disabled="sending"
                                @click.prevent="sendAccept"
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
                                @click.prevent="sendAccept"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-failure.svg') })` }"></span>
                                Принять с расхождениями
                            </button>
                        </div>
                        <div class="btn btn__icons">
                            <button
                                :disabled="!isAllProductsMatched || sending"
                                @click.prevent="send({ discharge: true })"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                Сохранить и отправить
                            </button>
                        </div>
                        <div class="btn btn__icons">
                            <button
                                :disabled="sending"
                                @click.prevent="send"
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
                v-if="formData.products.length"
                class="table_fixed-columns"
                content-style="position: relative;height: calc(100vh - 480px); min-height: 445px"
                :headers="table.headers"
                :items="table.items"
                :fixed-columns="true"
            >
                <template v-slot="{ headers, item, index }">
                    <div
                        v-for="(field, fieldIndex) in item.fields"
                        :key="fieldIndex"
                        class="col"
                        :style="headers[fieldIndex].style"
                    >
                        {{ field }}
                    </div>
                    <div class="col" :style="headers[3].style">
                        <SearchField
                            v-model="formData.products[index].nomenclature"
                            class="w-full"
                            :class="products.loading && '-preloader'"
                            :options="productsSearchData || []"
                            :fetch-data="findProducts"
                            :prefetch="true"
                            :tooltip="true"
                        />
                    </div>
                    <div class="col" :style="headers[4].style">
                        <SelectField
                            v-model="formData.products[index].unit"
                            :class="units.loading && '-preloader'"
                            :options="units.data || []"
                            :tooltip="true"
                            track-by="id"
                            :placeholder="headers[4].title"
                            label="title"
                        />
                    </div>
                    <div class="col" :class="'col-' + headers[5].style">
                        <InputField
                            :value="formData.products[index].comparisonRate"
                            mask="number"
                            :placeholder="headers[5].title"
                            @keyup="onComparisonRateInput($event, item.raw, index)"
                        />
                    </div>
                    <div class="col" :style="headers[6].style">
                        <InputField
                            :value="formData.products[index].quantity"
                            mask="number"
                            :placeholder="headers[6].title"
                            @keyup="onQuantityInput($event, item.raw, index)"
                        />
                    </div>
                    <div class="col" :style="headers[7].style">
                        <InputField
                            :value="formData.products[index].quantityFact"
                            mask="number"
                            :placeholder="headers[7].title"
                            @keyup="onQuantityFactInput($event, item.raw, index)"
                        />
                    </div>
                    <div class="col" :style="headers[8].style">
                        {{ formatNumber(item.raw.priceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                    </div>
                    <div class="col" :style="headers[9].style">
                        {{ formatNumber(item.raw.totalPriceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                    </div>
                    <div class="col" :style="headers[10].style">
                        {{ getTotalProductPrice(item.raw, index) }}
                    </div>
                </template>
            </BaseTable>
            <BaseTable
                v-if="invoice.data && (invoice.data.acceptanceAt || invoice.data.dischargeStatus)"
                :headers="tableFixed.headers"
                :items="tableFixed.items"
            />
        </div>
    </section>
</template>
<script>
import { cloneDeep } from 'lodash';
import { fetchProducts } from '@/api/product';
import {
    fetchInvoice,
    fetchInvoiceStatuses,
    sendInvoiceComparison,
    exchangeInvoice,
    sendInvoiceAccept
} from '@/api/invoices';
import { fetchUnits } from '@/api/units';
import { normalizeProductsForList } from '@/normalizers/products';
import formatDate from '@/helpers/formatDate';
import processError from '@/helpers/processError';
import formatNumber from '@/helpers/formatNumber';

export default {
    name: 'ProductsPage',
    fetch() {
        return Promise.all([
            this.fetchProducts(),
            this.fetchUnits()
        ]).then(() => this.fillFormData());
    },
    async asyncData({ route, redirect }) {
        let data = await fetchInvoice(route.params.id, {
            params: {
                withComparison: true
            }
        });

        if (data.iikoSend || data.storeHouseSend) {
            redirect({ name: 'invoice-id-matching-results', params: { id: route.params.id } });
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
            productsSearchData: [],
            formData: {
                products: []
            },
            formErrors: {},
            invoice: {
                data: null,
                loading: true
            },
            products: {
                data: null,
                loading: true
            },
            units: {
                data: null,
                loading: true
            },
            sending: false,
            formDataIsFilled: false
        };
    },
    computed: {
        showButtons() {
            return this.invoice.data && this.formDataIsFilled && (!this.invoice.data?.iikoSend && !this.invoice.data?.storeHouseSend);
        },
        isAllProductsMatched() {
            return this.formData.products?.every(item => !!item.nomenclature);
        },
        isAcceptFull() {
            return this.invoice.data.invoiceProducts.every((item, index) => {
                const coef = +this.formData.products[index].comparisonRate || 1;

                return item.quantity === (+this.formData.products[index].quantity * coef);
            });
        },
        table() {
            const headers = [
                {
                    title: '№',
                    style: 'width: 5%; max-width: 5%; flex-basis: 5%;'
                },
                {
                    title: 'Наименование',
                    style: 'width: 19%; max-width: 19%; flex-basis: 19%;'
                },
                {
                    title: 'Ед.изм.',
                    style: 'width: 7%; max-width: 7%; flex-basis: 7%;'
                },
                {
                    title: 'Наименование покупателя',
                    style: 'width: 20%; max-width: 20%; flex-basis: 20%;'
                },
                {
                    title: 'Ед.изм.',
                    style: 'width: 7%; max-width: 7%; flex-basis: 7%;'
                },
                {
                    title: 'Коэф.',
                    style: 'width: 7%; max-width: 7%; flex-basis: 7%;'
                },
                {
                    title: 'Принято факт',
                    style: 'width: 7%; max-width: 7%; flex-basis: 7%;'
                },
                {
                    title: 'Кол-во к учету',
                    style: 'width: 7%; max-width: 7%; flex-basis: 7%;'
                },
                {
                    title: 'Цена',
                    style: 'width: 7%; max-width: 7%; flex-basis: 7%;'
                },
                {
                    title: 'Сумма',
                    style: 'width: 7%; max-width: 7%; flex-basis: 7%;'
                },
                {
                    title: 'Сумма факт',
                    style: 'width: 7%; max-width: 7%; flex-basis: 7%;'
                }
            ];

            const items = this.invoice.data.invoiceProducts.map((item, index) => ({
                theme: this.formData.products.length && !this.isAllFilled(this.formData.products[index]) ? 'warning' : '',
                fields: [
                    index + 1,
                    item.product?.nomenclature,
                    item.unit?.title
                ],
                raw: item
            }));

            return {
                headers,
                items
            };
        },
        tableFixed() {
            const invoice = this.invoice.data;

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
        productsCached() {
            return cloneDeep(this.formData.products);
        }
    },
    methods: {
        formatDate,
        formatNumber,
        onComparisonRateInput(val, item, index) {
            if (val === this.formData.products[index].comparisonRate) return;

            this.formData.products[index].comparisonRate = val;
            this.formData.products[index].quantityFact = Number((this.formData.products[index].comparisonRate * this.formData.products[index].quantity).toFixed(3));
        },
        onQuantityInput(val, item, index) {
            if (val === this.formData.products[index].quantity) return;

            this.formData.products[index].quantity = val;
            this.formData.products[index].quantityFact = Number((this.formData.products[index].comparisonRate * this.formData.products[index].quantity).toFixed(3));
        },
        onQuantityFactInput(val, item, index) {
            if (val === this.formData.products[index].quantityFact) return;

            this.formData.products[index].quantityFact = val;
            this.formData.products[index].comparisonRate = Number((this.formData.products[index].quantityFact / this.formData.products[index].quantity).toFixed(2));
        },
        async sendAccept(options = {}) {
            this.sending = true;

            try {
                await sendInvoiceAccept(this.$route.params.id, {
                    products: this.formData.products.map((item, index) => ({
                        quantity: item.quantity,
                        invoiceProductId: this.invoice.data.invoiceProducts[index]?.id
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
            } catch (e) {
                console.log(e);
                processError(e);
            }

            this.sending = false;
        },
        async fetchInvoice() {
            this.loading = true;

            let data = await fetchInvoice(this.$route.params.id, {
                params: {
                    withComparison: true
                }
            });

            this.invoice = { data, loading: false };
        },
        async fetchProducts() {
            let data = await fetchProducts({
                params: { companyId: this.$auth.user.company.id }
            });

            this.products = { data: normalizeProductsForList(data), loading: false };
        },
        async fetchUnits() {
            let data = await fetchUnits();

            this.units = { data: this.normalizeUnits(data), loading: false };
        },
        async fetchDischarge() {
            let data = await fetchInvoiceStatuses({ params: {  code: 'discharge' } });

            this.discharge = { data, loading: false };
        },
        normalizeUnits(data) {
            return data.map(item => ({
                ...item,
                title: item.title + (item.fromIiko ? '(iiko)' : '')
            }));
        },
        fillFormData() {
            const invoice = this.invoice.data;

            this.formData = {
                products: invoice.invoiceProducts.map(item => ({
                    nomenclature: item.comparisonProduct?.product ? this.products.data.find(product => product.id === item.comparisonProduct.product.id) : null,
                    comparisonRate: item.comparisonProduct?.comparisonRate || 1,
                    unit: item.comparisonProduct?.unit || item.product?.unit,
                    quantity: item.comparisonProduct?.quantity || item.quantity,
                    quantityFact: item.comparisonProduct?.quantityFact || ((item.comparisonProduct?.quantity || item.quantity) * (item.comparisonProduct?.comparisonRate || 1)) || 1
                }))
            };

            this.formDataIsFilled = true;
        },
        getTotalProductPrice(item, index) {
            return formatNumber(this.formData.products[index].quantity * item.priceWithVat, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        async exchangeInvoice() {
            if (!this.isAllProductsMatched) return;

            try {
                await exchangeInvoice(this.$route.params.id);
                await this.$layer.alert({
                    message: 'Успешно'
                });
                this.$router.push({ name: 'invoice-id-matching-results', params: { id: this.$route.params.id } });
            } catch (e) {
                if (e?.response?.data?.error?.message) {
                    this.$layer.open('ErrorWithFeedbackLayer', { text: e?.response?.data?.error?.message });
                } else {
                    processError(e);
                }
            }
        },
        isAllFilled(item) {
            return Object.values(item).every(Boolean);
        },
        findProducts(search) {
            return this.products.data.filter(item => item.article.includes(search) || item.title?.toLowerCase().includes(search?.toLowerCase()));
        },
        async send(options = {}) {
            this.sending = true;

            if (Object.keys(this.formErrors).length) return;

            const formData = { ...this.formData };

            try {
                await sendInvoiceComparison(this.$route.params.id, {
                    ...formData,
                    products: this.formData.products.map((item, index) => ({
                        comparisonRate: item.comparisonRate,
                        quantity: item.quantity,
                        warehouseId: item.warehouse?.id,
                        unitId: item.unit?.id,
                        productId: item.nomenclature?.id,
                        quantityFact: item.quantityFact,
                        invoiceProductId: this.invoice.data.invoiceProducts[index]?.id
                    }))
                });

                await this.fetchInvoice();
                if (options.discharge) await this.exchangeInvoice();
                this.fillFormData();
                if (!options.discharge) {
                    await this.$layer.alert({
                        message: 'Успешно'
                    });
                }

                if (options.close) this.$router.push({ name: 'invoice' });
            } catch (e) {
                console.log(e);
                this.$layer.alert({
                    message: 'Ошибка'
                });
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
                title: 'Сопоставление'
            }
        ];
    }
};
</script>
