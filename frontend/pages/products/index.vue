<template>
    <section class="box__suppliers__page">
        <div class="container">
            <div class="row justify-between">
                <div class="col-4">
                    <h1>Номенклатура</h1>
                </div>
                <div class="col-3">
                    <div class="box__search">
                        <form @submit.prevent="formData.search = search">
                            <div class="box__input">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Поиск по таблице"
                                    @blur="formData.search = search"
                                >
                            </div>
                            <div class="btn__search">
                                <button></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div
                    v-if="$auth.user.isSupplier"
                    class="col-5"
                >
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn">
                            <button @click="$router.push({ name: 'products-export' })">
                                Экспорт товаров
                            </button>
                        </div>
                        <div class="btn">
                            <button @click="$router.push({ name: 'products-import' })">
                                Импорт товаров
                            </button>
                        </div>
                        <div class="btn">
                            <button @click="$router.push({ name: 'products-add' })">
                                Добавить товар
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <ProductNavigation />
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="aside__category">
                        <div class="row">
                            <div class="col-12">
                                <h4>Каталог</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div
                                    class="aside__category-catalog"
                                    :class="{
                                        '-preloader': categories.loading
                                    }"
                                >
                                    <ul>
                                        <Collapse
                                            v-for="category in categories.list"
                                            :key="category.id"
                                            class-name="li"
                                            :class="{
                                                '-preloader': category.isLoading
                                            }"
                                        >
                                            <template v-slot="{ isOpened, doToggle }">
                                                <div
                                                    v-if="category.isEdit"
                                                    class="aside__category-edit"
                                                >
                                                    <div class="box__form">
                                                        <form>
                                                            <InputField
                                                                :ref="`category-input-${ category.id }`"
                                                                v-model="category.title"
                                                                placeholder="Категория"
                                                                @keyup.native.esc="doCancelItem({ item: category })"
                                                            >
                                                                <template #side>
                                                                    <div class="btn btn__icon-purple">
                                                                        <button @click.prevent="doEditCategory(category)">
                                                                            <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                            </InputField>
                                                        </form>
                                                    </div>
                                                </div>
                                                <template v-else>
                                                    <div class="flex items-center justify-between">
                                                        <a
                                                            class="aside__category-title"
                                                            href="#"
                                                            :class="(formData.categories.includes(category.id) || category.children.find(item => formData.categories.includes(item.id))) && 'is-active-2'"
                                                            @click="filterByCategory(category.id)"
                                                        >
                                                            <span @click="doToggle">
                                                                {{ category.title }}
                                                            </span>
                                                        </a>
                                                        <div
                                                            class="btn btn__icon-purple"
                                                        >
                                                            <button @click.prevent="category.isEdit = true">
                                                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="aside__category-toggler"
                                                        :class="{ 'is-open': isOpened }"
                                                        @click="doToggle"
                                                    ></span>
                                                </template>
                                                <ul v-if="isOpened">
                                                    <li
                                                        v-for="child in category.children"
                                                        :key="child.id"
                                                        :class="{
                                                            'aside__category-edit': child.isEdit,
                                                            'aside__category-active': false // ToDo: поправить активный пункт
                                                        }"
                                                    >
                                                        <template v-if="child.isEdit">
                                                            <InputField
                                                                :ref="`sub-category-input-${ child.id }`"
                                                                v-model="child.title"
                                                                @keyup.native.esc="doCancelItem({ item: child })"
                                                            >
                                                                <template #side>
                                                                    <div class="btn btn__icon-purple">
                                                                        <button @click.prevent="doEditCategory(child)">
                                                                            <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                            </InputField>
                                                        </template>
                                                        <template v-else>
                                                            <a
                                                                class="aside__category-subitem"
                                                                :class="formData.categories.includes(child.id) && 'is-active'"
                                                                href="#"
                                                                @click="filterByCategory(child.id)"
                                                            >
                                                                {{ child.title }}
                                                            </a>
                                                            <div
                                                                class="btn btn__icon-purple"
                                                            >
                                                                <button @click.prevent="child.isEdit = true">
                                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </li>
                                                    <EditableRowWrapper
                                                        class="aside__category-edit"
                                                        tag="li"
                                                    >
                                                        <template v-slot="{ data, doClear }">
                                                            <form @submit.prevent="doSaveCategory({ item: { ...(data || {}), parentId: category.id }, doClear })">
                                                                <InputField v-model="data.title" placeholder="Категория">
                                                                    <template #side>
                                                                        <div class="btn btn__icon-purple">
                                                                            <button type="submit">
                                                                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                            </button>
                                                                        </div>
                                                                    </template>
                                                                </InputField>
                                                            </form>
                                                        </template>
                                                    </EditableRowWrapper>
                                                </ul>
                                            </template>
                                        </Collapse>
                                        <li>
                                            <EditableRowWrapper>
                                                <template v-slot="{ data, doClear }">
                                                    <div class="aside__category-edit">
                                                        <div class="box__form">
                                                            <form @submit.prevent="doSaveCategory({ item: { ...(data || {}), parentId: 0 }, doClear })">
                                                                <InputField
                                                                    ref="addCategoryInput"
                                                                    v-model="data.title"
                                                                    placeholder="Категория"
                                                                >
                                                                    <template #side>
                                                                        <div class="btn btn__icon-purple">
                                                                            <button type="submit">
                                                                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                            </button>
                                                                        </div>
                                                                    </template>
                                                                </InputField>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                            </EditableRowWrapper>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-9">
                    <ListController
                        ref="listController"
                        v-model="products"
                        :fetch-api="fetchProducts"
                        :fetch-options="fetchOptions"
                        :field-to-watch="formData"
                    >
                        <template v-slot="{ items }">
                            <BaseTable
                                :headers="table.headers"
                                :items="table.items"
                                :sorting="true"
                                :sort-direction="formData.sortDirection"
                                @sort="onSort"
                            >
                                <template #edit="{ index, headers }">
                                    <div :class="'col-' + headers[0].col"></div>
                                    <div :class="'col-' + headers[1].col">
                                        <InputField
                                            v-model="items[index].title"
                                            :placeholder="headers[1].title"
                                        />
                                    </div>
                                    <div :class="'col-' + headers[2].col">
                                        <SelectField
                                            v-if="!units.loading"
                                            :tooltip="true"
                                            :options="units.data"
                                            track-by="id"
                                            label="title"
                                        />
                                    </div>
                                    <div :class="'col-' + headers[3].col">
                                        <InputField
                                            v-model="items[index].article"
                                            placeholder="Артикул"
                                        />
                                    </div>
                                    <div :class="'col-' + headers[4].col">
                                        <InputField
                                            v-model="items[index].barcode"
                                            placeholder="Штрихкод"
                                        >
                                            <template v-slot:icon>
                                                <span class="input__icon" :style="{ backgroundImage: `url(${ require('@/assets/img/icon/barcode.svg') })` }"></span>
                                            </template>
                                        </InputField>
                                    </div>
                                    <div
                                        v-if="!headers[5].isHidden"
                                        :class="'col-' + headers[5].col"
                                    >
                                        <SelectField
                                            v-model="items[index].vat"
                                            :options="vatOptions"
                                            :tooltip="true"
                                            track-by="id"
                                            label="title"
                                        />
                                    </div>
                                    <div :class="'col-' + headers[6].col">
                                        <div class="wraapper__button-nowrap">
                                            <div class="btn btn__icon-purple">
                                                <button @click="save(items[index], true)">
                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                </button>
                                            </div>
                                            <div class="btn-whitepurple btn__icon-whitepurple">
                                                <button @click="doDeleteItem(items[index])">
                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #actions="{ index }">
                                    <div class="col-2">
                                        <div class="wraapper__button-nowrap">
                                            <div class="btn btn__icon-purple">
                                                <button @click="items[index].isEdit = true">
                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span>
                                                </button>
                                            </div>
                                            <div class="btn-whitepurple btn__icon-whitepurple">
                                                <button @click="doDeleteItem(items[index])">
                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </BaseTable>
                        </template>
                    </ListController>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { isObject, isNull } from 'lodash';
import { fetchProductCategories, addProductCategory, changeProductCategory, addProduct, fetchProducts, changeProduct, deleteProduct } from '@/api/product';
import { fetchUnits } from '@/api/units';
import { makeItemsEditable } from '@/normalizers/editable';
import { vatOptions } from '@/constants/products';

export default {
    name: 'ProductsPage',
    components: {},
    fetch() {
        this.fillFormData();

        return Promise.all([
            this.fetchCategories(),
            this.fetchUnits()
        ]);
    },
    data() {
        return {
            search: '',
            formData: {
                categories: [],
                search: '',
                sortField: '',
                sortDirection: 'DESC'
            },
            products: {
                data: null,
                loading: false,
                cancel: null
            },
            categories: {
                list: [],
                loading: false
            },
            units: {
                data: null,
                loading: true
            },
            vatOptions
        };
    },
    computed: {
        fetchOptions() {
            const {
                categories,
                search,
                sortField,
                sortDirection
            } = this.formData;

            return {
                companyId: this.$auth.user?.company?.id,
                categoriesId: categories,
                sortField,
                sortDirection,
                search,
                limit: 8
            };
        },
        table() {
            let headers = [
                {
                    title: 'id',
                    field: 'id',
                    col: 1
                },
                {
                    title: 'Наименование',
                    field: 'nomenclature',
                    col: 3
                },
                {
                    title: 'Ед. изм',
                    field: 'unit',
                    col: 1
                },
                {
                    title: 'Артикул',
                    field: 'article',
                    col: 2
                },
                {
                    title: 'Штрихкод',
                    field: 'barcode',
                    col: 2
                },
                {
                    title: 'НДС',
                    field: 'vat',
                    col: 1
                },
                {
                    title: 'Действие',
                    col: 2,
                    notSorting: true
                }
            ];

            headers = headers.map(item => item.notSorting ? item : ({ ...item, sortBy: this.formData.sortField === item.field }));

            const items = this.products.data.items.map(product => ({
                fields: [
                    product.id,
                    product.title,
                    product.unit?.title,
                    product.article,
                    product.barcode,
                    product.vat.title
                ],
                isEdit: product.isEdit,
                loading: product.isLoading
            }));

            return {
                headers,
                items
            };
        }
    },
    methods: {
        async fetchProducts(...config) {
            const data = await fetchProducts(...config);

            return this.normalizeProducts(data);
        },
        normalizeProducts(data) {
            return {
                ...data,
                items: data.items.map(item => ({
                    id: item.id,
                    title: item.nomenclature,
                    article: item.article,
                    barcode: item.barcode,
                    unit: item.unit,
                    vat: { id: item.vat, title: item.vat + '%' },
                    isEdit: false,
                    isLoading: false,
                    brandId: item.brand?.id,
                    manufacturerId: item.manufacturer?.id,
                    categoryId: item.category.id,
                    price: item.price
                }))
            };
        },
        async fetchCategories() {
            this.categories.loading = true;

            return fetchProductCategories()
                .then(response => {
                    this.categories.list = makeItemsEditable(response);
                })
                .finally(() => {
                    this.categories.loading = false;
                });
        },
        async fetchUnits() {
            const data = await fetchUnits();

            this.units = { data, loading: false };
        },
        onSort(field) {
            if (this.formData.sortField === field) {
                this.formData.sortDirection = this.formData.sortDirection === 'DESC' ? 'ASC' : 'DESC';
            } else {
                this.formData.sortField = field;
                this.formData.sortDirection = 'DESC';
            }
        },
        // @click.prevent="doItemEdit({ item: child, inputRefPrefix: 'sub-category-input' })"
        doItemEdit({ item, inputRefPrefix } = {}) {
            this.doToggleEditable(item, true);
            this.$nextTick(() => {
                if (item.id && inputRefPrefix) {
                    const inputComponent = this.$refs[`${ inputRefPrefix }-${ item.id }`];
                    inputComponent?.[0].$refs?.input?.focus();
                }
            });
        },
        doCancelItem({ item } = {}) {
            this.doToggleEditable(item, false);
        },
        doToggleEditable(item, state) {
            if (!isObject(item) || isNull(item)) return;
            item.isEdit = state;
        },
        async doEditCategory(item) {
            if (!item.title) return;

            item.isLoading = true;

            try {
                await changeProductCategory(item.id, { title: item.title });
            } catch (e) {
                console.log('Unable to change category', e);
            }
            item.isLoading = false;
            item.isEdit = false;
        },
        async doSaveCategory(data) {
            const item = data?.item;
            if (!item || !item.title) return;
            item.isLoading = true;

            try {
                await addProductCategory(item, {
                    progress: false
                });
                await this.fetchCategories();
                if (typeof data.doClear === 'function') {
                    data.doClear();
                }
            } catch (e) {
                console.log('Unable to add category', e);
            }
            item.isLoading = false;
        },
        getItemIndex(id) {
            return this.products.data.items.findIndex(_item => _item.id === id);
        },
        async save(item, list = false) {
            let result = {
                ...item,
                unitId: item.unit?.id,
                vat: item.vat?.id,
                nomenclature: item.title,
                list: list
            };

            item.isLoading = true;
            delete result.unit;

            await changeProduct(item.id, result);

            item.isEdit = false;
            item.isLoading = false;
        },
        async doDeleteItem(item) {
            if (item) {
                const index = this.getItemIndex(item.id);

                await deleteProduct(item.id);

                this.products.data.items.splice(index, 1);
            }
        },
        async doAddProduct(data, doClear) {
            console.log('doAddProduct:data', data);

            try {
                const result = await addProduct(data);
                console.log('result', result);
                if (typeof doClear === 'function') {
                    doClear();
                }
            } catch (e) {
                console.log('Unable to add product', e);
            }
        },
        filterByCategory(id) {
            this.formData.categories = [id];
            this.fetchProducts();
        },
        fillFormData() {
            let { search, sortField, sortDirection, categories  } = this.$route.query;

            categories = categories ? (Array.isArray(categories) ? categories : [categories]).map(Number) : [];

            this.formData = {
                categories: categories,
                search: search || '',
                sortField: sortField || '',
                sortDirection: sortDirection || 'DESC'
            };
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Номенклатура'
            }
        ];
    }
};
</script>
