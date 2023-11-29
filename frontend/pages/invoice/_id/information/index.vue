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
                    v-if="formDataIsFilled"
                    class="col-12 mt-30"
                >
                    <div class="wraapper__button-nowrap content__justify-start">
                        <div
                            v-if="isAcceptFull && !invoice.data.acceptanceStatus"
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
                            v-else-if="!invoice.data.acceptanceStatus"
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
                        <div
                            v-if="!invoice.data.acceptanceStatus"
                            class="btn btn-red btn__icons"
                        >
                            <button
                                @click.prevent="sendAccept({ cancel: true, close: true })"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-cancel.svg') })` }"></span>
                                Аннулировать и закрыть
                            </button>
                        </div>
                        <div
                            v-if="!invoice.data.iikoSend && !invoice.data.storeHouseSend"
                            class="btn btn__icons"
                        >
                            <button
                                :disabled="!isAllProductsMatched || sending"
                                @click.prevent="sendMatching({ discharge: true })"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                Сохранить и отправить
                            </button>
                        </div>
                        <div class="btn btn-green btn__icons">
                            <button
                                :disabled="sending"
                                @click.prevent="sendMatching"
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
            <div class="row">
                <div class="col-9">
                    <div class="box__form">
                        <form>
                            <div class="row">
                                <div class="col-6">
                                    <div class="box__select">
                                        <SelectField
                                            v-model="formData.counterpartyId"
                                            :class="counterparties.loading && '-preloader'"
                                            :options="counterparties.data || []"
                                            track-by="id"
                                            placeholder="Контрагент из учетной записи"
                                            label="title"
                                        />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="box__select">
                                        <SelectField
                                            v-model="formData.warehouseId"
                                            :class="warehouses.loading && '-preloader'"
                                            :options="warehouses.data || []"
                                            track-by="id"
                                            placeholder="Склад"
                                            label="title"
                                        />
                                    </div>
                                </div>
                                <div class="col-12">
                                    <TextareaField
                                        v-model="formData.comment"
                                        placeholder="Комментарий"
                                    />
                                </div>
                                <div class="col-12">
                                    <TextareaField
                                        v-model="formData.messageSupplier"
                                        placeholder="Сообщение поставщику"
                                    />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { fetchProducts } from '@/api/product';
import { fetchWarehouses } from '@/api/warehouses';
import { fetchInvoice, sendInvoiceComparison, exchangeInvoice, sendInvoiceAccept } from '@/api/invoices';
import { fetchCounterparties } from '@/api/counterparties';
import { normalizeProductsForList } from '@/normalizers/products';
import formatDate from '@/helpers/formatDate';
import processError from '@/helpers/processError';

export default {
    name: 'ProductsPage',
    fetch() {
        return Promise.all([
            this.fetchCounterparties(),
            this.fetchProducts(),
            this.fetchWarehouses()
        ]).then(() => this.fillFormData());
    },
    async asyncData({ route, redirect }) {
        let data = await fetchInvoice(route.params.id, {
            params: {
                withComparison: true
            }
        });

        if (data.acceptanceStatus && (data.iikoSend || data.storeHouseSend)) {
            redirect({ name: 'invoice-id-information-results', params: { id: route.params.id } });
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
            sending: false,
            formDataIsFilled: false,
            formData: {
                products: [],
                counterpartyId: null,
                warehouseId: null,
                comment: null,
                messageSupplier: null
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
            counterparties: {
                data: null,
                loading: true
            },
            warehouses: {
                data: null,
                loading: true
            }
        };
    },
    computed: {
        showButtons() {
            return !this.invoice.data?.iikoSend && !this.invoice.data?.storeHouseSend;
        },
        isAllProductsMatched() {
            return this.formData.products?.every(item => !!item.nomenclature);
        },
        isAcceptFull() {
            return this.formDataIsFilled && this.invoice.data.invoiceProducts.every((item, index) => {
                const coef = +item.comparisonProduct?.comparisonRate || 1;

                return item.quantity === (+this.formData.products[index].quantity * coef);
            });
        }
    },
    methods: {
        formatDate,
        async fetchInvoice() {
            this.loading = true;

            let data = await fetchInvoice(this.$route.params.id, {
                params: {
                    withComparison: true
                }
            });

            this.invoice = { data, loading: false };
        },
        async fetchCounterparties() {
            let data = await fetchCounterparties();

            this.counterparties = { data, loading: false };
        },
        async fetchWarehouses() {
            let data = await fetchWarehouses();

            this.warehouses = { data, loading: false };
        },
        async fetchProducts() {
            let data = await fetchProducts({
                params: { companyId: this.$auth.user.company.id }
            });

            this.products = { data: normalizeProductsForList(data), loading: false };
        },
        fillFormData() {
            const invoice = this.invoice.data;

            this.formData = {
                counterpartyId: this.counterparties.data.find(item => item.id === invoice.counterparty?.id),
                warehouseId: this.warehouses.data.find(item => item.id === invoice.warehouse?.id),
                comment: invoice.comment,
                messageSupplier: invoice.messageSupplier,
                products: invoice.invoiceProducts.map(item => ({
                    nomenclature: item.comparisonProduct?.product ? this.products.data.find(product => product.id === item.comparisonProduct.product.id) : null,
                    comparisonRate: item.comparisonProduct?.comparisonRate || 1,
                    unit: item.comparisonProduct?.unit || item.product?.unit,
                    quantity: item.comparisonProduct?.quantity || item.quantity,
                    warehouse: item.comparisonProduct?.warehouse
                }))
            };

            this.formDataIsFilled = true;
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
        async sendMatching(options = {}) {
            this.sending = true;

            if (Object.keys(this.formErrors).length) return;

            const formData = { ...this.formData };

            try {
                await sendInvoiceComparison(this.$route.params.id, {
                    ...formData,
                    products: this.formData.products.map((item, index) => ({
                        comparisonRate: item.comparisonRate,
                        quantity: item.quantity,
                        unitId: item.unit?.id,
                        productId: item.nomenclature?.id,
                        invoiceProductId: this.invoice.data.invoiceProducts[index]?.id
                    })),
                    counterpartyId: this.formData.counterpartyId?.id,
                    warehouseId: this.formData.warehouseId?.id
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
        isAllFilled(item) {
            return Object.values(item).every(Boolean);
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
                        unitId: item.unit?.id,
                        productId: item.nomenclature?.id,
                        invoiceProductId: this.invoice.data.invoiceProducts[index]?.id
                    })),
                    discharge: options.discharge,
                    cancel: options.cancel
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
