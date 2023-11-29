<template>
    <div class="box__app__main">
        <main role="main">
            <section class="box__buyers__page">
                <div class="container">
                    <div class="row">
                        <div class="col-5">
                            <h1>Номенклатура</h1>
                        </div>
                        <div
                            v-if="$auth.user.isSupplier"
                            class="col-7"
                        >
                            <div class="wraapper__button-nowrap content__justify-end">
                                <div class="btn">
                                    <button
                                        :disabled="!brands.data || !canAdd"
                                        @click="add"
                                    >
                                        Добавить бренд
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
                        <div class="col-12">
                            <div class="box__content__table">
                                <div class="table__content-title">
                                    <div class="row">
                                        <div class="col-10">
                                            <h3>Наименование</h3>
                                        </div>
                                        <div
                                            class="col-2"
                                        >
                                            <h3>Действие</h3>
                                        </div>
                                    </div>
                                </div>
                                <ListController
                                    ref="listController"
                                    v-model="brands"
                                    class="table__content"
                                    :fetch-api="fetchProductBrands"
                                    :fetch-options="fetchOptions"
                                >
                                    <template v-slot="{ items }">
                                        <div
                                            v-for="(item, index) in items"
                                            :key="index"
                                            class="box__table__item"
                                        >
                                            <div
                                                class="box__item"
                                                :class="item.isEdit && 'box__item-edit'"
                                                data-table
                                            >
                                                <div class="row">
                                                    <div class="col-10">
                                                        <template v-if="!item.isEdit">
                                                            {{ item.title }}
                                                        </template>
                                                        <InputField
                                                            v-else
                                                            v-model="formData.title"
                                                            placeholder="Бренд"
                                                            :error="formErrors.title"
                                                        />
                                                    </div>
                                                    <div
                                                        v-if="$auth.user.isSupplier"
                                                        class="col-2"
                                                    >
                                                        <div class="wraapper__button-nowrap">
                                                            <div class="btn btn__icon-purple">
                                                                <button
                                                                    v-if="item.isEdit"
                                                                    @click="save(item)"
                                                                >
                                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                </button>
                                                                <button
                                                                    v-else
                                                                    @click="doEditItem(item)"
                                                                >
                                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span>
                                                                </button>
                                                            </div>
                                                            <div class="btn-whitepurple btn__icon-whitepurple">
                                                                <button @click="doDeleteItem(item)">
                                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span>
                                                                </button>
                                                            </div>
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
</template>
<script>
import  { addProductBrands, fetchProductBrands, changeProductBrands, deleteProductBrands } from '@/api/product';
import { makeItemEditable } from '@/normalizers/editable';
import validateMixin from '@/mixins/validateMixin';

export default {
    mixins: [
        validateMixin('formData', 'formErrors', {
            required: {
                title: 'Заполните поле'
            }
        })
    ],
    data() {
        return {
            brands: {
                data: null,
                loading: false,
                cancel: null
            },
            formData: {
                title: ''
            },
            formErrors: {},
            fetchOptions: {
                limit: 8
            }
        };
    },
    computed: {
        canAdd() {
            return !this.brands.data.items.find(item => !('id' in item));
        }
    },
    methods: {
        add() {
            this.brands.data.items.unshift(makeItemEditable({
                title: ''
            }));
            this.brands.data.items[0].isEdit = true;
        },
        async fetchProductBrands(...config) {
            const data = await fetchProductBrands(...config);

            return this.normalizeProductBrands(data);
        },
        normalizeProductBrands(data) {
            return {
                ...data,
                items: data.items.map(makeItemEditable)
            };
        },
        getItemIndex(item) {
            return this.brands.data.items.findIndex(_item => item.id ? _item.id === item.id : item === item);
        },
        async save(item) {
            this.validateFormData();
            if (Object.keys(this.formErrors).length) return;

            if (item.id) {
                await changeProductBrands(item.id, {
                    title: this.formData.title
                });
            } else if (this.formData.title) {
                await addProductBrands({
                    title: this.formData.title
                });
            }

            await this.$router.push({ query: { page: 1 } });
            this.$refs.listController.fetchData();
            this.formData.title = '';
        },
        async doDeleteItem(item) {
            const index = this.getItemIndex(item);

            if (item.id) {
                await deleteProductBrands(item.id);
                await this.$router.push({ query: { page: 1 } });
                this.$refs.listController.fetchData();
            } else {
                this.brands.data.items.splice(index, 1);
            }
        },
        doEditItem(item) {
            this.formData.title = item.title;
            item.isEdit = true;
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
                title: 'Бренды'
            }
        ];
    }
};
</script>
