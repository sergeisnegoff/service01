ц<template>
    <div class="box__app__content">
        <div class="box__app__main">
            <main role="main">
                <section class="box__buyers__page">
                    <div v-if="products.loading || units.loading" class="-preloader"></div>
                    <div class="container">
                        <div class="row">
                            <div class="col-5">
                                <h1>Импорт товаров</h1>
                            </div>
                            <div class="col-7">
                                <div class="wraapper__button-nowrap content__justify-end">
                                    <div
                                        v-if="step !== 1"
                                        class="btn btn__icons"
                                    >
                                        <button @click="step -= 1">
                                            <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-arrow-left.svg') })` }"></span>
                                            Назад
                                        </button>
                                    </div>
                                    <div class="btn btn__icons">
                                        <button
                                            :disabled="nextButtonDisabled"
                                            @click="goToStep(step + 1)"
                                        >
                                            <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-arrow-right.svg') })` }"></span>
                                            {{ step !== 3 ? 'Следующий шаг' : 'Начать импорт' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="box__tabs">
                                    <ul>
                                        <li
                                            :class="step === 1 && 'active'"
                                            @click="goToStep(1)"
                                        >
                                            1. Выбор файла
                                        </li>
                                        <li
                                            :class="step === 2 && 'active'"
                                            @click="goToStep(2)"
                                        >
                                            <a href="#">
                                                2. Сопоставление
                                            </a>
                                        </li>
                                        <li
                                            :class="step === 3 && 'active'"
                                            @click="goToStep(3)"
                                        >
                                            <a href="#">
                                                3. Импорт товаров
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <template v-if="step === 1">
                            <div class="row">
                                <div class="col-3">
                                    <br>
                                    <h4>Файл для импорта</h4>
                                    <div class="btn btn__icons margin-15">
                                        <button @click="$refs.firstStepFile.click()">
                                            <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-file.svg') })` }"></span>
                                            {{ formData.file ? 'Изменить файл' : 'Выбрать файл' }}
                                        </button>
                                        <div
                                            v-if="formErrors.file"
                                            class="text-red-500 mt-2"
                                        >
                                            {{ formErrors.file }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-3">
                                    <h4>Импортировать данные для</h4>
                                    <div class="box__select">
                                        <SelectField
                                            v-model="formData.category"
                                            :class="{ '-preloader': categories.loading }"
                                            :options="categories.data || []"
                                            placeholder="Выбрать категорию"
                                            track-by="id"
                                            label="title"
                                        />
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-if="step === 2">
                            <div class="row">
                                <div class="col-12">
                                    <div class="box__content__table" style="padding: 0 0 0 45px;">
                                        <div class="table__content-title">
                                            <div
                                                class="row row_nowrap"
                                                :class="{
                                                    'overflow-x-scroll': products.headers.length > 6
                                                }"
                                            >
                                                <div class="box__col-num">
                                                    №
                                                </div>
                                                <div
                                                    v-for="(header, index) in products.headers"
                                                    :key="index"
                                                    class="col-2"
                                                >
                                                    <div class="box__select box__select-title">
                                                        <SelectField
                                                            v-if="!mappingFields.loading"
                                                            v-model="formData.mappingFields[index]"
                                                            :options="mappingFields.data"
                                                            :tooltip="true"
                                                            track-by="id"
                                                            label="title"
                                                        />
                                                    </div>
                                                    <h3>
                                                        {{ header }}
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table__content">
                                            <div
                                                v-for="(item, index) in products.data.slice(0, 4)"
                                                :key="index"
                                                class="box__table__item"
                                            >
                                                <div
                                                    class="box__item"
                                                    data-table
                                                >
                                                    <div
                                                        class="row row_nowrap"
                                                        :class="{
                                                            'overflow-x-scroll': item.items.length > 6
                                                        }"
                                                    >
                                                        <div class="box__col-num">
                                                            {{ index + 1 }}
                                                        </div>
                                                        <div
                                                            v-for="(col, colIndex) in item.items"
                                                            :key="colIndex"
                                                            class="col-2"
                                                        >
                                                            {{ col }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-if="step === 3">
                            <div class="row margin-15">
                                <div class="col-3">
                                    <h4>Уникальный идентификатор</h4>
                                    <div class="box__select">
                                        <SelectField
                                            v-if="!mappingFields.loading"
                                            v-model="formData.uniqId"
                                            :options="mappingFields.data.filter(item => item.id !== 'unit' && formData.mappingFields.find(_item => _item && _item.id === item.id))"
                                            track-by="id"
                                            label="title"
                                            :error="formErrors.uniqId"
                                        />
                                        <span class="text__select-sub">Идентификатор, по которому мы понимаем, что товар уникальный</span>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-12">
                                    <div class="box__checkbox">
                                        <div class="wrapper-checkbox">
                                            <label>
                                                <input v-model="formData.insert" type="checkbox" checked>
                                                <span>
                                                    <span class="box__checkbox-icon"></span>
                                                    <span class="box__checkbox-text">Создать новые элементы содержащиеся в файле</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="box__checkbox">
                                        <div class="wrapper-checkbox">
                                            <label>
                                                <input v-model="formData.deleteOther" type="checkbox">
                                                <span>
                                                    <span class="box__checkbox-icon"></span>
                                                    <span class="box__checkbox-text">Удалить остальные элементы, которые не присутствуют в данном файле в выбранной категории</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="box__checkbox box__checkbox-step">
                                        <div class="wrapper-checkbox">
                                            <label>
                                                <input v-model="formData.update" type="checkbox">
                                                <span>
                                                    <span class="box__checkbox-icon"></span>
                                                    <span class="box__checkbox-text">Обновить существующие элементы информацией из файла</span>
                                                </span>
                                                <div class="box__checkbox">
                                                    <div class="wrapper-checkbox">
                                                        <label>
                                                            <input v-model="formData.updateNomenclature" type="checkbox">
                                                            <span>
                                                                <span class="box__checkbox-icon"></span>
                                                                <span class="box__checkbox-text">Наименование</span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="box__checkbox">
                                                    <div class="wrapper-checkbox">
                                                        <label>
                                                            <input v-model="formData.updateUnit" type="checkbox">
                                                            <span>
                                                                <span class="box__checkbox-icon"></span>
                                                                <span class="box__checkbox-text">Ед. измерения</span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="box__checkbox">
                                                    <div class="wrapper-checkbox">
                                                        <label>
                                                            <input v-model="formData.updateBarcode" type="checkbox">
                                                            <span>
                                                                <span class="box__checkbox-icon"></span>
                                                                <span class="box__checkbox-text">Штрихкод</span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </main>
        </div>
        <input
            ref="firstStepFile"
            class="hidden"
            type="file"
            @change="formData.file = $event.target.files ? $event.target.files[0] : null"
        >
    </div>
</template>
<script>
import { fetchProductCategories } from '@/api/product';
import { fetchProductsImportMappingFields, productsImportParse, productsImport, setProductsImportMapping } from '@/api/productsImport';
import { fetchUnits } from '@/api/units';
import createFormData from '@/helpers/createFormData';
import processError from '@/helpers/processError';
import { makeItemEditable } from '@/normalizers/editable';

export default {
    name: 'ProductSuppliersPage',
    fetch() {
        return Promise.all([
            this.fetchCategories(),
            this.fetchProductsImportMappingFields()
        ]);
    },
    data() {
        return {
            formData: {
                category: null,
                file: null,
                uniqId: null,
                mappingFields: [],
                insert: false,
                deleteOther: false,
                update: false,
                updateNomenclature: false,
                updateUnit: false,
                updateBarcode: false
            },
            formErrors: {},
            categories: {
                data: null,
                loading: true
            },
            mappingFields: {
                data: null,
                loading: true
            },
            units: {
                data: null,
                loading: false
            },
            products: {
                headers: null,
                data: null,
                loading: false
            },
            id: null,
            sending: false,
            step: 1
        };
    },
    computed: {
        normalizedFormData() {
            return {
                ...this.formData,
                categoryId: this.formData.category?.id
            };
        },
        nextButtonDisabled() {
            if (this.step === 1) {
                return !this.formData.file || !this.formData.category;
            }

            if (this.step === 2) {
                return !this.formData.mappingFields.filter(Boolean).length;
            }

            if (this.step === 3) {
                const { insert, deleteOther, updateNomenclature, updateUnit, updateBarcode } = this.formData;

                return !(insert || deleteOther || updateNomenclature || updateUnit || updateBarcode);
            }

            return false;
        }
    },
    watch: {
        'formData.insert'(val) {
            if (val) {
                this.formData.deleteOther = false;
                this.formData.update = false;
            }
        },
        'formData.deleteOther'(val) {
            if (val) {
                this.formData.insert = false;
                this.formData.update = false;
            }
        },
        'formData.update'(val) {
            console.log(val);
            if (!val) {
                this.formData.updateNomenclature = false;
                this.formData.updateUnit = false;
                this.formData.updateBarcode = false;
            } else {
                this.formData.deleteOther = false;
                this.formData.insert = false;
            }
        }
    },
    methods: {
        async fetchUnits() {
            this.units.loading = true;

            const data = await fetchUnits();

            this.units = { data, loading: false };
        },
        async fetchCategories() {
            const data = await fetchProductCategories();
            const result = [];

            data.forEach(item => {
                result.push(item);

                if (item.children) result.push(...item.children.map(item => ({ ...item, title: ' ' + item.title, isSub: true })));
            });

            this.categories = { data: result, loading: false };
        },
        async fetchProductsImportMappingFields() {
            let data = await fetchProductsImportMappingFields();

            data = Object.entries(data).map(item => {
                return {
                    id: item[0],
                    title: item[1]
                };
            });

            this.mappingFields = { data, loading: false };
        },
        async goToStep(num) {
            if (num === 1) {
                this.step = 1;
            } else if (num === 2) {
                if (this.step === 1 && !this.nextButtonDisabled) {
                    try {
                        await Promise.all([
                            await this.productsImportParse(),
                            !this.units.data && await this.fetchUnits()
                        ]);
                        this.step = 2;
                    } catch (e) {
                        processError(e, this.formErrors);
                        this.products.loading = false;
                    }
                }
                if (this.step === 3) this.step = 2;
            } else if (num === 3) {
                if (this.step === 1 && this.formData.mappingFields.filter(Boolean).length && this.checkIfUnitIsSelected()) this.step = 3;
                if (this.step === 2 && !(!this.formData.file || !this.formData.category) && this.checkIfUnitIsSelected()) this.step = 3;
            } else if (num === 4) {
                this.importAll();
            }
        },
        async productsImportParse() {
            this.products.loading = true;

            const data = await productsImportParse(createFormData({
                file: this.formData.file,
                categoryId: this.formData.category.id
            }));

            this.id = data.id;
            this.products = { data: data.rows.map(items => (makeItemEditable({ items }))), headers: data.header, loading: false };
        },
        doDeleteItem(item) {
            if (!item) return;

            const index = this.products.data.findIndex(_item => _item === item);

            this.products.data.splice(index, 1);
        },
        async importAll() {
            const formData = { ...this.formData };

            delete formData.mappingFields;
            delete formData.category;
            delete formData.file;

            try {
                await setProductsImportMapping(this.id, { map: this.formData.mappingFields.map((item, index) => item.id + '_' + (index + 1)) });
                await productsImport(this.id, {
                    ...formData,
                    uniqId: formData.uniqId?.id
                });
                await this.$layer.alert({
                    message: 'Успешно'
                });
                this.$router.push({ name: 'products' });
            } catch (e) {
                processError(e, this.formErrors);
            }

        },
        checkIfUnitIsSelected() {
            const isSelected = !!this.formData.mappingFields.find(item => item?.id === 'unit');

            if (!isSelected) this.$layer.alert({
                title: 'Ошибка',
                message: 'Сопоставьте единицу измерения'
            });

            return isSelected;
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
                title: 'Импорт'
            }
        ];
    }
};
</script>
