<template>
    <section class="box__buyers__page invoice-page">
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <h1>Создание накладной</h1>
                </div>
            </div>
            <div class="row mt-30 align-center">
                <div class="col-2">
                    <div class="box__input box__input-no-margin">
                        <span class="input__icon" :style="{ backgroundImage: `url(${require('@/assets/img/icon/calendar.svg')})` }"></span>
                        <DatePicker
                            v-model="formData.createdAt"
                            placeholder="Дата накладной"
                        />
                    </div>
                </div>
                <div class="col-3">
                    <SelectField
                        v-model="formData.buyer"
                        :class="{ '-preloader': buyers.loading }"
                        :options="buyers.data || []"
                        placeholder="Выберите покупателя"
                        track-by="id"
                        label="title"
                    />
                </div>
                <div class="col-3">
                    <SelectField
                        v-model="formData.shop"
                        :class="{ '-preloader': shops.loading }"
                        :options="shops.data || []"
                        :disabled="!formData.buyer"
                        placeholder="Выберите торговую точку"
                        track-by="id"
                        label="title"
                    />
                </div>
                <div class="col-4">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn btn__icons">
                            <button @click="addItem">
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-add.svg') })` }"></span>
                                Добавить товар
                            </button>
                        </div>
                        <div class="btn btn__icons btn-green">
                            <button
                                :disabled="!formData.shop"
                                @click="save"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                Сохранить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="box__content__table table-invoice">
                        <div class="table__content-title box__table-sorting">
                            <div class="row">
                                <div
                                    class="col"
                                    style="width: 5%; max-width: 5%; flex-basis: 5%;"
                                >
                                    <h3>№</h3>
                                </div>
                                <div
                                    class="col"
                                    style="width: 20%; max-width: 20%; flex-basis: 20%;"
                                >
                                    <h3>Наименование товара</h3>
                                </div>
                                <div
                                    class="col"
                                    style="width: 10%; max-width: 10%; flex-basis: 10%;"
                                >
                                    <h3>Ед.изм.</h3>
                                </div>
                                <div
                                    class="col"
                                    style="width: 5%; max-width: 5%; flex-basis: 5%;"
                                >
                                    <h3>Кол.</h3>
                                </div>
                                <div
                                    class="col"
                                    style="width: 12.5%; max-width: 12.5%; flex-basis: 12.5%;"
                                >
                                    <h3>Цена</h3>
                                </div>
                                <div
                                    class="col"
                                    style="width: 12.5%; max-width: 12.5%; flex-basis: 12.5%;"
                                >
                                    <h3>Сумма</h3>
                                </div>
                                <div
                                    class="col"
                                    style="width: 12.5%; max-width: 12.5%; flex-basis: 12.5%;"
                                >
                                    <h3>
                                        Ставка НДС
                                    </h3>
                                </div>
                                <div
                                    class="col"
                                    style="width: 12.5%; max-width: 12.5%; flex-basis: 12.5%;"
                                >
                                    <h3>Сумма НДС</h3>
                                </div>
                                <div
                                    class="col"
                                    style="width: 10%; max-width: 10%; flex-basis: 10%;"
                                >
                                    <h3>
                                        Действие
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="table__content">
                            <template v-if="formData.products.length">
                                <div
                                    v-for="(item, index) in formData.products"
                                    :key="index"
                                    class="box__table__item"
                                >
                                    <div class="box__item" data-table>
                                        <div class="row">
                                            <div
                                                class="col"
                                                style="width: 5%; max-width: 5%; flex-basis: 5%;"
                                            >
                                                {{ index + 1 }}
                                            </div>
                                            <div
                                                class="col"
                                                style="width: 20%; max-width: 20%; flex-basis: 20%;"
                                            >
                                                <div class="box__select">
                                                    <SearchField
                                                        v-if="products.data"
                                                        v-model="item.productId"
                                                        class="w-full"
                                                        placeholder="Наименование товара"
                                                        :options="productsSearchData || []"
                                                        :fetch-data="findProducts"
                                                        :prefetch="true"
                                                    />
                                                </div>
                                            </div>
                                            <div
                                                class="col"
                                                style="width: 10%; max-width: 10%; flex-basis: 10%;"
                                            >
                                                <SelectField
                                                    v-model="item.unit"
                                                    :options="units.data || []"
                                                    :class="{ '-preloader': units.loading }"
                                                    track-by="id"
                                                    placeholder="Единица измерения"
                                                    label="title"
                                                />
                                            </div>
                                            <div
                                                class="col"
                                                style="width: 5%; max-width: 5%; flex-basis: 5%;"
                                            >
                                                <InputField
                                                    v-model="item.quantity"
                                                    placeholder="Количество"
                                                    mask="number"
                                                />
                                            </div>
                                            <div
                                                class="col"
                                                style="width: 12.5%; max-width: 12.5%; flex-basis: 12.5%;"
                                            >
                                                <InputField
                                                    v-model="item.price"
                                                    placeholder="Цена"
                                                    mask="number"
                                                />
                                            </div>
                                            <div
                                                class="col"
                                                style="width: 12.5%; max-width: 12.5%; flex-basis: 12.5%;"
                                            >
                                                <InputField
                                                    v-model="item.totalPrice"
                                                    placeholder="Сумма"
                                                    mask="number"
                                                />
                                            </div>
                                            <div
                                                class="col"
                                                style="width: 12.5%; max-width: 12.5%; flex-basis: 12.5%;"
                                            >
                                                <SelectField
                                                    v-if="item.productId"
                                                    v-model="item.productId.vat"
                                                    :options="vatOptions"
                                                    :tooltip="true"
                                                    track-by="id"
                                                    label="title"
                                                    placeholder="Ставка НДС"
                                                />
                                            </div>
                                            <div
                                                class="col"
                                                style="width: 12.5%; max-width: 12.5%; flex-basis: 12.5%;"
                                            >
                                                <InputField
                                                    v-if="item.productId"
                                                    v-model="item.totalPriceWithVat"
                                                    placeholder="Сумма НДС"
                                                    mask="number"
                                                />
                                            </div>
                                            <div
                                                v-if="index > 0"
                                                class="col"
                                                style="width: 10%; max-width: 10%; flex-basis: 10%;"
                                            >
                                                <div class="btn btn__icon-white border-purple">
                                                    <button @click="remove(index)">
                                                        <span
                                                            :style="{backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })`}"
                                                        >
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-else>
                                Добавьте товар
                            </div>
                        </div>
                        <div class="table__content-bottom">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { cloneDeep, isEmpty, isEqual } from 'lodash';
import { createInvoice } from '@/api/invoices';
import { fetchProducts } from '@/api/product';
import { fetchUnits } from '@/api/units';
import { fetchBuyersList, fetchBuyerShops } from '@/api/buyer';
import { vatOptions } from '@/constants/products';
import validateMixin from '@/mixins/validateMixin';
import { formatNumberWriting } from '@/helpers/formatNumber';

export default {
    name: 'InvoicePage',
    mixins: [
        validateMixin('formData', 'formErrors', {
            required: {
                products: [{ iikoSupplierExternalCode: 'Заполните поле' }]
            }
        })
    ],
    fetch() {
        return Promise.all([
            this.fetchProducts(),
            this.fetchUnits(),
            this.fetchBuyers()
        ]);
    },
    data: () => ({
        vatOptions,
        productsSearchData: [],
        formData: {
            products: [],
            buyer: null,
            shop: null
        },
        formErrors: {},
        products: {
            data: null,
            loading: true
        },
        buyers: {
            data: null,
            loading: true
        },
        shops: {
            data: null,
            loading: false
        },
        units: {
            data: null,
            loading: true
        }
    }),
    computed: {
        productsCached() {
            return cloneDeep(this.formData.products);
        }
    },
    watch: {
        'formData.buyer'(val) {
            if (val) this.fetchBuyerShops();
        },
        productsCached: {
            deep: true,
            handler(val, prevVal) {
                if (isEmpty(val) || isEmpty(prevVal) || isEqual(val, prevVal)) return;

                val.forEach((item, index) => {
                    const prevItem = { ...prevVal[index] };

                    if (!prevItem) return;

                    if ((item.quantity !== prevItem.quantity || item.price !== prevItem.price) && (prevItem.quantity !== undefined && prevItem.price !== undefined)) {
                        this.formData.products[index].totalPrice = (item.price * item.quantity).toFixed(2);
                    }

                    if (this.formData.products[index].totalPrice !== prevItem.totalPrice || item.productId?.vat?.id !== prevItem.productId?.vat?.id) {
                        this.formData.products[index].totalPriceWithVat = (item.totalPrice * ((item.productId?.vat.id + 100) / 100)).toFixed(2);
                    }
                });
            }
        }
    },
    created() {
        this.addItem();
    },
    methods: {
        async fetchProducts() {
            const data = await fetchProducts({
                params: {
                    companyId: this.$auth.user?.company?.id
                }
            });

            this.products = { data: this.normalizeProducts(data), loading: false };
        },
        async fetchUnits() {
            const data = await fetchUnits();

            this.units = { data, loading: false };
        },
        async fetchBuyerShops() {
            this.shops.loading = true;

            const data = await fetchBuyerShops({
                params: {
                    companyId: this.formData.buyer.id
                }
            });

            this.shops = { data, loading: false };
        },
        async fetchBuyers() {
            const data = await fetchBuyersList({ params: { myBuyers: true } });

            this.buyers = { data, loading: false };
        },
        getPriceWithVat(item) {
            return ((formatNumberWriting(item.price)) * formatNumberWriting(item.quantity) * (1 + (item.productId?.vat?.id || 0) / 100)).toFixed(2);
        },
        getPrice(item) {
            return ((formatNumberWriting(item.price)) * formatNumberWriting(item.quantity)).toFixed(2);
        },
        addItem() {
            this.formData.products.unshift({
                productId: null,
                quantity: '',
                price: '',
                vat: '',
                unit: null,
                totalPrice: '',
                totalPriceWithVat: ''
            });
        },
        normalizeProducts(data) {
            return data.map(item => ({
                id: item.id,
                title: item.nomenclature,
                unit: item.unit,
                article: item.article,
                vat: item.vat ? { id: item.vat, title: item.vat + '%' } : vatOptions[0]
            }));
        },
        async save() {
            this.sending = true;

            try {
                await createInvoice({
                    createdAt: this.formData.createdAt,
                    buyerId: this.formData.buyer?.id,
                    shopId: this.formData.shop.id,
                    products: this.formData.products.map(item => ({
                        productId: item.productId?.id,
                        quantity: item.quantity,
                        price: item.price,
                        totalPrice: item.totalPrice,
                        totalPriceWithVat: item.totalPriceWithVat,
                        vat: item.productId?.vat?.id,
                        unitId: item.productId?.unit?.id
                    })).filter(item => item.productId !== undefined)
                });

                await this.$layer.alert({ message: 'Успешно!' });
                this.$router.push({ name: 'invoice' });
            } catch (e) {
                console.log(e);
            }

            this.sending = false;
        },
        findProducts(search) {
            return this.products.data.filter(item => item.article.includes(search) || item.title?.toLowerCase().includes(search?.toLowerCase()));
        },
        remove(index) {
            this.formData.products.splice(index, 1);
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Накладные',
                link: { name: 'invoice' }
            },
            {
                title: 'Добавить накладную'
            }
        ];
    }
};
</script>
