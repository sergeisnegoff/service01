<template>
    <section class="box__companyuser__page">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <h1>Моя организация</h1>
                </div>
                <div class="col-6">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn btn__icons">
                            <button @click="canAddShop = !canAddShop">
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-add.svg') })` }"></span>
                                Добавить торговую точку
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <CompanyNavigation />
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="box__content__table">
                        <div class="table__content-title">
                            <div class="row">
                                <div class="col-4">
                                    <h3>
                                        Название
                                    </h3>
                                </div>
                                <div class="col-4">
                                    <h3>
                                        Адрес
                                    </h3>
                                </div>
                                <div class="col-3">
                                    <h3>
                                        Координаты
                                    </h3>
                                </div>
                                <div class="col-1">
                                    <h3>
                                        Действия
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div
                            class="table__content"
                            :class="{
                                '-preloader': list.loading
                            }"
                        >
                            <div
                                v-for="item in list.items"
                                :key="item.id"
                                class="box__table__item"
                                :class="{
                                    '-preloader': item.isLoading
                                }"
                            >
                                <div class="box__item">
                                    <div
                                        v-if="item.isEdit"
                                        class="box__item-edit"
                                        @keyup.esc="doCancelItem(item)"
                                    >
                                        <div class="box__form">
                                            <form @submit.prevent="doSaveShop(item.id)">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <InputField
                                                            v-model="item.title"
                                                            placeholder="Название"
                                                            :error="item.errors.title"
                                                        />
                                                    </div>
                                                    <div class="col-4">
                                                        <DadataAddressField
                                                            v-model="item.address"
                                                            :restrictions="[
                                                                {
                                                                    country_iso_code: 'RU'
                                                                }
                                                            ]"
                                                            :dadata-formatter="dadataAddressFormatter"
                                                            bound="street"
                                                            placeholder="Указать на карте"
                                                            :error="item.errors.address"
                                                        />
                                                    </div>
                                                    <div class="col-3">
                                                        {{ item.coordinates }}
                                                    </div>
                                                    <div class="col-1">
                                                        <div class="wraapper__button-nowrap">
                                                            <div class="btn btn__icon-purple">
                                                                <button @click="doSaveShop(item.id)">
                                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                </button>
                                                            </div>
                                                            <div class="btn-whitepurple btn__icon-whitepurple">
                                                                <button type="button" @click="onRemoveShop({ item })">
                                                                    <span :style="{ backgroundImage: `url('${ require('@/assets/img/icon/btn-trash.svg') }')` }"></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <div class="row">
                                            <div class="col-4">
                                                {{ item.title }}
                                            </div>
                                            <div class="col-4">
                                                {{ item.addressTitle }}
                                            </div>
                                            <div class="col-3">
                                                {{ item.coordinates }}
                                            </div>
                                            <div class="col-1">
                                                <div class="wraapper__button-nowrap">
                                                    <div class="btn btn__icon-purple">
                                                        <button @click="doEditItem( item)">
                                                            <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span>
                                                        </button>
                                                    </div>
                                                    <div class="btn-whitepurple btn__icon-whitepurple">
                                                        <button type="button" @click="onRemoveShop({item})">
                                                            <span :style="{ backgroundImage: `url('${ require('@/assets/img/icon/btn-trash.svg') }')` }"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <OrganizationAddShop
                                v-if="canAddShop"
                                class="box__table__item"
                                :class="{
                                    '-preloader': organizationShopAdding
                                }"
                            >
                                <template v-slot="{ data, doClear }">
                                    <div data-table class="box__item box__item-edit active">
                                        <div class="box__form">
                                            <form @submit.prevent="addShop({ item: data, clearForm: doClear })">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <InputField
                                                            v-model="data.title"
                                                            placeholder="Название"
                                                            :error="organizationShopAddErrors.title"
                                                        />
                                                    </div>
                                                    <div class="col-4">
                                                        <DadataAddressField
                                                            v-model="data.address"
                                                            :restrictions="[
                                                                {
                                                                    country_iso_code: 'RU'
                                                                }
                                                            ]"
                                                            :dadata-formatter="dadataAddressFormatter"
                                                            bound="street"
                                                            placeholder="Адрес"
                                                            :error="organizationShopAddErrors.address || organizationShopAddErrors.latitude || organizationShopAddErrors.longitude"
                                                        />
                                                    </div>
                                                    <div class="col-3"></div>
                                                    <div class="col-1">
                                                        <div class="wraapper__button-nowrap">
                                                            <div class="btn btn__icon-purple">
                                                                <button type="submit">
                                                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </OrganizationAddShop>
                        </div>
                        <div class="table__content-bottom">
                            <div class="row">
                                <div class="col-12">
                                    <Pagination
                                        v-if="list.pagination.pages"
                                        :pages="Number(list.pagination.pages)"
                                        :page="Number(list.pagination.page)"
                                        @paginate="onPaginate"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import {
    fetchBuyerShops,
    removeBuyerOrganizationShop,
    addBuyerOrganizationShop,
    changeBuyerOrganizationShop,
    approveOrganizationsShopsFromSmart,
    importOrganizationsShopsFromSmart
} from '@/api/buyer';
import {
    normalizeOrganizations,
    normalizeOrganizationShop,
    normalizeOrganizationShops
} from '@/normalizers/organization';
import { addressFormatter as dadataAddressFormatter } from '@/normalizers/dadata';
import Error from '@/helpers/error';
import processError from '@/helpers/processError';

export default {
    name: 'CompanyOrganizations',
    fetch() {
        return Promise.all([
            this.fetchList(),
            this.showSmartIfFirstImport()
        ]);
    },
    data() {
        return {
            canAddShop: false,
            organizationAdding: false,
            organizationShopAdding: false,
            isSmartBlockShown: false,
            importingSmart: false,
            approvingSmart: false,
            organizationShopAddErrors: {
                title: '',
                address: '',
                latitude: '',
                longitude: ''
            },
            listFromSmart: {
                items: [],
                loading: false,
                cancel: null
            },
            smartFormData: {
                shopIds: []
            },
            list: {
                items: [],
                pagination: {
                    page: 1,
                    pages: 0
                },
                loading: false,
                cancel: null,
                limit: 5
            },
            listFormData: {
            },
            shopFormData: {
                title: '',
                address: ''
            }
        };
    },
    computed: {
        currentCompanyId() {
            return this.$auth.user?.company?.id;
        }
    },
    methods: {
        dadataAddressFormatter,
        async fetchList() {
            const page = this.list.pagination.page || 1;
            const limit = this.list.limit;

            this.list.loading = true;

            return fetchBuyerShops({
                params: {
                    ...(this.listFormData || {}),
                    companyId: this.currentCompanyId,
                    page,
                    limit
                }
            })
                .then(response => {
                    this.list.items = normalizeOrganizationShops(response.items);
                    this.list.pagination = response.pagination;
                    this.list.loading = false;
                });
        },
        async fetchShopsFromSmart() {
            this.listFromSmart.loading = true;

            let data = await fetchBuyerShops({
                params: {
                    companyId: this.currentCompanyId,
                    smart: true
                }
            });

            this.listFromSmart = { items: normalizeOrganizations(data), loading: false  };
        },
        fillSmartFormData() {
            this.listFromSmart.items.forEach(item => {
                item.shops.forEach(shop => this.smartFormData.shopIds.push(shop.id));
            });
        },
        toggleSmartOrganizationCheckboxes(id) {
            const organization = this.listFromSmart.items.find(item => item.id === id);
            const isAllChecked = organization.shops.every(item => this.smartFormData.shopIds.includes(item.id));

            if (!isAllChecked) {
                organization.shops.forEach(item => {
                    if (!this.smartFormData.shopIds.includes(item.id)) this.smartFormData.shopIds.push(item.id);
                });
            } else {
                organization.shops.forEach(item => {
                    const findIndex = this.smartFormData.shopIds.findIndex(shopId => item.id === shopId);

                    if (findIndex + 1) this.smartFormData.shopIds.splice(findIndex, 1);
                });
            }
        },
        isSmartOrganizationChecked(id) {
            const organization = this.listFromSmart.items.find(item => item.id === id);

            return organization.shops.every(item => this.smartFormData.shopIds.includes(item.id));
        },
        async approveOrganizationsShopsFromSmart() {
            this.approvingSmart = false;

            try {
                await approveOrganizationsShopsFromSmart(this.smartFormData);
                this.$layer.alert({
                    title: 'Данные успешно сохранены'
                });
                this.fetchList();
                this.isSmartBlockShown = false;
                this.$parent.$parent.$parent.$refs.scroll.$el.scrollTop = 0;
            } catch (e) {
                console.log(e);
                processError(e);
            }

            this.approvingSmart = false;
        },
        async toggleSmartBlock(showIfSuccess = false) {
            let success = false;
            if (this.isSmartBlockShown) {
                this.isSmartBlockShown = false;

                return;
            }

            this.importingSmart = true;

            try {
                success = await importOrganizationsShopsFromSmart();

                if (showIfSuccess && !success) {
                    this.importingSmart = false;

                    return;
                }

                await this.fetchShopsFromSmart();
                this.fillSmartFormData();
            } catch (e) {
                console.log(e);
                processError(e);
            }

            this.importingSmart = false;

            if (showIfSuccess && !success) return;
            if (!this.listFromSmart.items?.length) {
                this.$layer.alert({
                    title: 'Данные из Smart Pro отсутствуют'
                });

                return;
            }

            this.isSmartBlockShown = true;
        },
        async showSmartIfFirstImport() {
            if (this.$auth.user.company.isCompleteFirstImportSmart && this.$auth.user.isBuyer) return;

            this.$auth.setUser({
                ... this.$auth.user,
                company: {
                    ...this.$auth.user.company,
                    isCompleteFirstImportSmart: true
                }
            });

            //await this.toggleSmartBlock(true);
        },

        onPaginate(page) {
            this.list.pagination.page = page;
            this.fetchList();
        },
        doEditItem(item) {
            if (!item) return;
            item.isEdit = true;
        },
        doCancelItem(item) {
            if (!item) return;
            item.isEdit = false;
        },
        async doSaveShop(id) {
            if (!id) return;

            let itemIndex = this.list.items.findIndex(item => item.id === id);
            let item = this.list.items[itemIndex];

            item.isLoading = true;

            const address = item.address;
            const data = {
                title: item.title,
                address: address?.title,
                latitude: address?.latitude,
                longitude: address?.longitude,
                docrobotExternalCode: item.docrobotExternalCode,
                diadocExternalCode: item.diadocExternalCode
            };

            try {
                const result = await changeBuyerOrganizationShop(item.id, data);

                if (result) {
                    item = normalizeOrganizationShop(result);
                }

                this.list.items.splice(itemIndex, 1, item);
            } catch (e) {
                console.log(e);
                processError(e, this.errors);
            }

            item.isLoading = false;
        },
        async addShop({ item, clearForm } = {}) {
            this.organizationShopAddErrors = {};

            if (!item) return;
            const address = item.address;
            this.organizationShopAdding = true;
            try {
                await addBuyerOrganizationShop({
                    title: item.title,
                    address: address?.title,
                    latitude: address?.latitude,
                    longitude: address?.longitude,
                    docrobotExternalCode: item.docrobotExternalCode,
                    diadocExternalCode: item.diadocExternalCode
                });
                if (typeof clearForm === 'function') {
                    clearForm();
                }
                this.fetchList();
            } catch (e) {
                const error = Error.normalize(e);
                if (error?.message) {
                    this.$layer.alert({
                        title: 'Ошибка',
                        message: error.message
                    });
                } else {
                    this.organizationShopAddErrors = error;
                }
                console.log('error', error);
                console.log('Unable to add organization shop', e);
            }
            this.organizationShopAdding = false;
        },
        async onRemoveShop({ item } = {}) {
            if (!item) return;
            const removeConfirm = await this.$layer.confirm({
                title: 'Подтверждение',
                message: 'Вы уверены что хотите удалить торговую точку?'
            });
            if (!removeConfirm) return;

            item.isLoading = true;
            try {
                await removeBuyerOrganizationShop(item.id);
                await this.fetchList();
            } catch (e) {
                const error = Error.normalize(e);
                if (error?.message) {
                    this.$layer.alert({
                        title: 'Ошибка',
                        message: error.message
                    });
                }
                console.log('Unable to remove organization', e);
            }
            item.isLoading = false;
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Торговые точки'
            }
        ];
    }
};
</script>
