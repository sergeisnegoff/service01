<template>
    <div class="box__app__content">
        <div class="box__app__main">
            <main role="main">
                <section class="box__suppliers__page">
                    <div class="container">
                        <div class="row">
                            <div class="col-9">
                                <h1>Добавить товар</h1>
                            </div>
                            <div class="col-3">
                                <BaseButton
                                    class="text-right"
                                    :disabled="sending"
                                    @click="add"
                                >
                                    Сохранить
                                </BaseButton>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="suppliers__addproduct">
                                    <div class="row">
                                        <div class="col-12">
                                            <h2>
                                                Основные
                                            </h2>
                                        </div>
                                    </div>
                                    <div class="box__form">
                                        <div v-if="sending" class="-preloader"></div>
                                        <form>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="box__input">
                                                        <InputField
                                                            v-model="formData.nomenclature"
                                                            placeholder="Номенклатура(наименование)"
                                                            :error="formErrors.nomenclature"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="box__select">
                                                        <SelectField
                                                            v-model="formData.unitId"
                                                            :options="units.data"
                                                            :class="{ '-preloader': units.loading }"
                                                            track-by="id"
                                                            placeholder="Единица измерения"
                                                            label="title"
                                                            :error="formErrors.unitId"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="box__input">
                                                        <InputField
                                                            v-model="formData.article"
                                                            placeholder="Артикул"
                                                            :error="formErrors.article"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="box__input">
                                                        <span class="input__icon" :style="{ backgroundImage: `url(${ require('@/assets/img/icon/barcode.svg') })` }"></span>
                                                        <InputField
                                                            v-model="formData.barcode"
                                                            placeholder="Штрихкод"
                                                            :error="formErrors.barcode"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="box__select">
                                                        <SelectField
                                                            v-model="formData.vat"
                                                            :options="formInfo.vat"
                                                            track-by="id"
                                                            placeholder="НДС"
                                                            label="title"
                                                            :error="formErrors.vat"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="box__select">
                                                        <SelectField
                                                            v-model="formData.categoryId"
                                                            :options="categories.data"
                                                            :class="{ '-preloader': categories.loading }"
                                                            track-by="id"
                                                            placeholder="Категория"
                                                            label="title"
                                                            :error="formErrors.categoryId"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="box__select">
                                                        <SelectField
                                                            v-model="formData.manufacturerId"
                                                            :options="manufacturers.data"
                                                            :class="{ '-preloader': manufacturers.loading }"
                                                            track-by="id"
                                                            placeholder="Производитель"
                                                            label="title"
                                                            :error="formErrors.manufacturerId"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="box__select">
                                                        <SelectField
                                                            v-model="formData.brandId"
                                                            :options="brands.data"
                                                            :class="{ '-preloader': brands.loading }"
                                                            track-by="id"
                                                            placeholder="Бренд"
                                                            label="title"
                                                            :error="formErrors.brandId"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>

<script>
import { addProduct, fetchProductBrands, fetchProductManufacturers, fetchProductCategories } from '@/api/product';
import { fetchUnits } from '@/api/units';
import { vatOptions } from '@/constants/products';
import processError from '@/helpers/processError';
import validateMixin from '@/mixins/validateMixin';

export default {
    name: 'ProductSuppliersPage',
    mixins: [validateMixin('formData', 'formErrors', {})],
    fetch() {
        return Promise.all([
            this.fetchProductBrands(),
            this.fetchProductManufacturers(),
            this.fetchUnits(),
            this.fetchProductCategories()
        ]);
    },
    data() {
        return {
            formData: {
                unitId: null,
                brandId: null,
                vat: null,
                manufacturerId: null,
                categoryId: null
            },
            formErrors: {},
            formInfo: {
                vat: vatOptions
            },
            sending: false,
            brands: {
                data: [],
                loading: true
            },
            manufacturers: {
                data: [],
                loading: true
            },
            units: {
                data: [],
                loading: true
            },
            categories: {
                data: [],
                loading: true
            }
        };
    },
    computed: {
        normalizedFormData() {
            return {
                ...this.formData,
                unitId: this.formData.unitId?.id,
                brandId: this.formData.brandId?.id,
                manufacturerId: this.formData.manufacturerId?.id,
                categoryId: this.formData.categoryId?.id,
                vat: this.formData.vat?.id
            };
        }
    },
    methods: {
        async fetchProductBrands() {
            const data = await fetchProductBrands();

            this.brands = { data, loading: false };
        },
        async fetchProductManufacturers() {
            const data = await fetchProductManufacturers();

            this.manufacturers = { data, loading: false };
        },
        async fetchUnits() {
            const data = await fetchUnits();

            this.units = { data, loading: false };
        },
        async fetchProductCategories() {
            const data = await fetchProductCategories();

            const result = [];

            data.forEach(item => {
                result.push(item);
                if (item.children) result.push(...item.children.map(item => ({ ...item, title: ' ' + item.title, isSub: true })));
            });

            this.categories = { data: result, loading: false };
        },
        async add() {
            this.sending = true;

            try {
                await addProduct(this.normalizedFormData);
                this.$layer.alert({
                    message: 'Товар успешно добавлен'
                });
                this.clearFormData();
            } catch (e) {
                processError(e, this.formErrors);
            }

            this.sending = false;
        },
        clearFormData() {
            for (let key in this.formData) {
                this.formData[key] = '';
            }
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Номенклатура',
                link: {
                    name: 'products'
                }
            },
            {
                title: 'Добавить товар'
            }
        ];
    }
};
</script>
