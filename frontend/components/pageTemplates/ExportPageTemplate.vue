<template>
    <div class="box__app__content">
        <div class="box__app__main">
            <main role="main">
                <section class="box__suppliers__page">
                    <div class="container">
                        <div class="row">
                            <div class="col-5">
                                <h1>
                                    Экспорт товаров
                                </h1>
                            </div>
                            <div class="col-7">
                                <div class="wraapper__button-nowrap content__justify-end">
                                    <div class="btn btn__icons">
                                        <button @click="checkAll">
                                            <span :style="{backgroundImage: `url(${ require('@/assets/img/icon/table-checkall.svg') })`}"></span>
                                            Выбрать все
                                        </button>
                                    </div>
                                    <div class="btn btn__icons">
                                        <button @click="uncheckAll">
                                            <span :style="{backgroundImage: `url(${ require('@/assets/img/icon/table-uncheckall.svg') })`}"></span>
                                            Снять выделение
                                        </button>
                                    </div>
                                    <div class="btn btn__icons">
                                        <button
                                            :disabled="!exportButton.isEnabled"
                                            @click="exportFields"
                                        >
                                            <span :style="{backgroundImage: `url(${ require('@/assets/img/icon/table-file.svg') })`}"></span>
                                            Экспортировать в Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-3">
                                <div
                                    class="aside__category"
                                    :class="categories.loading && '-preloader'"
                                >
                                    <div class="row">
                                        <div class="col-12">
                                            <h4>Каталог</h4>
                                        </div>
                                    </div>
                                    <div
                                        v-if="categories.data && categories.data.length && formData"
                                        class="row"
                                    >
                                        <div class="col-12">
                                            <div class="aside__category-catalog aside__category-export">
                                                <ul>
                                                    <li
                                                        v-for="(item, index) in categories.data"
                                                        :key="index"
                                                        :class="formData.categories.includes(item.id) && 'aside__category-add'"
                                                    >
                                                        <div class="box__checkbox">
                                                            <div class="wrapper-checkbox">
                                                                <label @click.prevent="toggleCatergoriesItem(item)">
                                                                    <input
                                                                        type="checkbox"
                                                                        :checked="formData.categories.includes(item.id)"
                                                                    >
                                                                    <span>
                                                                        <span class="box__checkbox-icon"></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <a
                                                            href="#"
                                                            @click.prevent="toggleCatergoriesItem(item)"
                                                        >
                                                            {{ item.title }}
                                                        </a>
                                                        <span
                                                            v-if="item.children.length"
                                                            class="aside__category-toggler"
                                                            :class="{ 'is-open': item.isOpen }"
                                                            @click="item.isOpen = !item.isOpen"
                                                        >
                                                        </span>
                                                        <ul v-if="item.children.length && item.isOpen">
                                                            <li
                                                                v-for="(subitem, subindex) in item.children"
                                                                :key="subindex"
                                                            >
                                                                <div class="box__checkbox">
                                                                    <div class="wrapper-checkbox">
                                                                        <label @click.prevent="toggleCatergoriesItem(subitem)">
                                                                            <input
                                                                                :checked="formData.categories.includes(subitem.id)"
                                                                                type="checkbox"
                                                                            >
                                                                            <span>
                                                                                <span class="box__checkbox-icon"></span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <a
                                                                    href="#"
                                                                    @click.prevent="toggleCatergoriesItem(subitem)"
                                                                >
                                                                    {{ subitem.title }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        Нет категорий
                                    </div>
                                </div>
                            </div>
                            <div class="col-9">
                                <div
                                    class="box__content__table"
                                    :class="{ '-preloader': products.loading }"
                                >
                                    <ListController
                                        v-if="formData"
                                        v-model="products"
                                        class="table__content"
                                        :class="sending && '-preloader'"
                                        :fetch-api="fetchProducts"
                                        :fetch-options="fetchOptions"
                                        :field-to-watch="formData"
                                    >
                                        <template v-slot="{ items }">
                                            <div class="table__content-title box__table-export">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <div class="box__checkbox">
                                                            <div class="wrapper-checkbox">
                                                                <label>
                                                                    <input v-model="exportFormData.fields" value="nomenclature" type="checkbox">
                                                                    <span>
                                                                        <span class="box__checkbox-icon"></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <h3>Номенклатура</h3>
                                                    </div>
                                                    <div class="col-2">
                                                        <div class="box__checkbox">
                                                            <div class="wrapper-checkbox">
                                                                <label>
                                                                    <input v-model="exportFormData.fields" value="unit" type="checkbox">
                                                                    <span>
                                                                        <span class="box__checkbox-icon"></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <h3>Ед. изм</h3>
                                                    </div>
                                                    <div class="col-2">
                                                        <div class="box__checkbox">
                                                            <div class="wrapper-checkbox">
                                                                <label>
                                                                    <input v-model="exportFormData.fields" value="article" type="checkbox">
                                                                    <span>
                                                                        <span class="box__checkbox-icon"></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <h3>Артикул</h3>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="box__checkbox">
                                                            <div class="wrapper-checkbox">
                                                                <label>
                                                                    <input v-model="exportFormData.fields" value="barcode" type="checkbox">
                                                                    <span>
                                                                        <span class="box__checkbox-icon"></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <h3>Шрихкод</h3>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="table__content">
                                                <div class="box__table__item box__table-export">
                                                    <div
                                                        v-for="(product, index) in items"
                                                        :key="index"
                                                        class="box__item"
                                                        data-table
                                                    >
                                                        <div class="row">
                                                            <div class="col-4">
                                                                <div class="box__checkbox">
                                                                    <div class="wrapper-checkbox">
                                                                        <label>
                                                                            <input
                                                                                :value="product.id"
                                                                                :checked="exportFormData.all ? !exportFormData.productsId.includes(product.id) : exportFormData.productsId.includes(product.id)"
                                                                                type="checkbox"
                                                                                @change="selectProduct(product.id)"
                                                                            >
                                                                            <span>
                                                                                <span class="box__checkbox-icon"></span>
                                                                                <span class="box__checkbox-text"></span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                {{ product.title }}
                                                            </div>
                                                            <div class="col-2">
                                                                {{ product.unit.title }}
                                                            </div>
                                                            <div class="col-2">
                                                                {{ product.article }}
                                                            </div>
                                                            <div class="col-4">
                                                                {{ product.barcode }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </ListController>
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
import { fetchProducts } from '@/api/product';
import { normalizeProductsForTable } from '@/normalizers/products';
import exportMixin from '@/mixins/exportMixin';
import validateMixin from '@/mixins/validateMixin';

export default {
    name: 'ProductSuppliersPage',
    mixins: [
        exportMixin,
        validateMixin('formData', 'formErrors', {})
    ],
    props: {
        categoriesApi: {
            type: Function
        }
    },
    fetch() {
        return Promise.all([
            this.fetchCategories().then(() => this.fillFormData()),
            this.fetchProductsExportFields()
        ]);
    },
    data() {
        return {
            sending: false,
            products: {
                data: null,
                loading: true,
                cancel: null
            },
            formData: null,
            categories: {
                data: null,
                loading: true
            }
        };
    },
    computed: {
        exportFormDataCompanyId() {
            return this.$route.params.id;
        },
        fetchOptions() {
            return {
                companyId: this.$route.params.id || this.$auth.user?.company?.id,
                categoriesId: this.formData.categories,
                limit: 8
            };
        }
    },
    methods: {
        async fetchProducts(...config) {
            const data = await fetchProducts(...config);

            return normalizeProductsForTable(data);
        },
        async fetchCategories() {
            let data = await this.categoriesApi({
                params: {
                    companyId: this.$route.params.id
                }
            });

            data = data.map(item => ({
                ...item,
                isOpen: false
            }));

            this.categories = { data, loading: false };
        },
        fillFormData() {
            let categories = this.$route.query.categories;
            let collectedCategories = this.categories.data.reduce((acc, item) => {
                acc.push(item.id);

                if (item.children) item.children.forEach(child => acc.push(child.id));

                return acc;
            }, []);

            categories = categories ? (Array.isArray(categories) ? categories : [categories]).map(Number) : [];

            this.formData = {
                categories: categories.length ? categories : collectedCategories
            };
        },
        toggleCatergoriesItem(item) {
            if (!this.formData.categories.includes(item.id)) {
                this.formData.categories.push(item.id);
            } else {
                this.formData.categories = this.formData.categories.filter(id => id !== item.id);
            }

            this.fetchProducts();
        }
    }
};
</script>
