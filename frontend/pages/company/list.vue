<template>
    <section class="box__suppliers__page">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <h1>Мои организации</h1>
                </div>
                <div class="col-4">
                    <div class="flex flex-0 justify-end ml-3">
                        <BaseButton
                            v-if="!isAdding"
                            @click="onAddCompany"
                        >
                            Добавить организацию
                        </BaseButton>
                        <BaseButton
                            v-if="isAdding"
                            @click="onAddCompanySave"
                        >
                            Сохранить
                        </BaseButton>
                        <BaseButton
                            v-if="isAdding"
                            class="ml-2"
                            @click="onAddCompanyCancel"
                        >
                            Отменить
                        </BaseButton>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="box__content__table">
                        <div class="table__content-title">
                            <div class="row">
                                <div class="col-5">
                                    <h3>
                                        Название
                                    </h3>
                                </div>
                                <div class="col-5">
                                    <h3>
                                        Активность
                                    </h3>
                                </div>
                                <div class="col-2">
                                    <h3>
                                        Сменить владельца
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <ListController
                            ref="listController"
                            v-model="list"
                            class="table__content"
                            :fetch-api="fetchList"
                            :fetch-options="fetchOptions"
                            @after-prefetch="showSmartLayer"
                        >
                            <template v-slot="{ items }">
                                <div
                                    v-for="item in items"
                                    :key="item.id"
                                    class="box__table__item cursor-pointer select-none"
                                    @click="onSelectCompany(item.id)"
                                >
                                    <div
                                        class="box__item border-2 border-transparent"
                                        :class="{
                                            'bg-green-100 border-green-500': activeCompanyId === item.id
                                        }"
                                    >
                                        <div class="row">
                                            <div class="col-5">
                                                {{ item.title }}
                                            </div>
                                            <div class="col-5 flex">
                                                <InputSwitch
                                                    :value="item.visible"
                                                    @change="onChangeCompanyVisibility(item)"
                                                    @click.native.stop
                                                />
                                            </div>
                                            <div class="col-2">
                                                <div class="wrapper__buttons content__justify-start">
                                                    <div class="btn btn__icon-purple">
                                                        <button @click.stop="onChangeCompanyOwners(item.id)">
                                                            <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-user.svg')} )` }"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template #additional>
                                <div
                                    v-if="isAdding"
                                    class="box__table__item"
                                >
                                    <div class="box__item">
                                        <div class="row">
                                            <div class="col-4">
                                                <InputField
                                                    v-model="formData.title"
                                                    class="flex-1 px-2"
                                                    :error="formErrors.title"
                                                    placeholder="Название*"
                                                />
                                            </div>
                                            <div class="col-3">
                                                <InputField
                                                    v-model="formData.inn"
                                                    class="flex-1 px-2"
                                                    :error="formErrors.inn"
                                                    placeholder="ИНН*"
                                                />
                                            </div>
                                            <div class="col-3">
                                                <SelectField
                                                    v-model="formData.typeCode"
                                                    :options="formInfo.typeCodes.options"
                                                    track-by="id"
                                                    placeholder="Тип организации"
                                                    label="title"
                                                    :error="formErrors.typeCode"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="showCompanyJoinInn"
                                    class="box__form-alert alert-horizontal"
                                >
                                    <div class="row">
                                        <div class="col-10">
                                            <div class="icon__alert">
                                                <span></span>
                                            </div>
                                            <div class="description-alert">
                                                <p>Организация с таким ИНН уже существует, отправить запрос на присоединение?</p>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div
                                                class="btn text-right"
                                            >
                                                <a
                                                    :class="{ '-preloader': isJoiningInn }"
                                                    href="#"
                                                    @click.prevent="companyJoinInn"
                                                >
                                                    Отправить заявку
                                                </a>
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
</template>
<script>
import { fetchCompanyList, selectCompany, addCompany, changeCompanyVisibility, companyJoinInn } from '@/api/company';
import { importOrganizationsShopsFromSmart } from '@/api/buyer';
import validateMixin from '@/mixins/validateMixin';
import processError from '@/helpers/processError';

export default {
    name: 'CompanyList',
    mixins: [
        validateMixin('formData', 'formErrors', {
            required: {
                title: 'Значение не должно быть пустым.',
                inn: 'Значение не должно быть пустым.',
                typeCode: 'Значение не должно быть пустым.'
            }
        })
    ],
    data() {
        return {
            isJoiningInn: false,
            isAdding: false,
            addLoading: false,
            showCompanyJoinInn: false,
            formData: {},
            formErrors: {},
            formInfo: {
                typeCodes: {
                    options: [
                        {
                            title: 'Поставщик',
                            id: 'supplier'
                        },
                        {
                            title: 'Покупатель',
                            id: 'buyer'
                        }
                    ]
                }
            },
            list: {
                data: null,
                loading: false,
                cancel: null
            },
            fetchOptions: {
                limit: 8
            }
        };
    },
    computed: {
        activeCompanyId() {
            return this.$auth.user?.company?.id;
        },
        normalizedFormData() {
            return {
                ...this.formData,
                typeCode: this.formData.typeCode?.id
            };
        }
    },
    created() {
        this.resetFormData();
    },
    async mounted() {

    },
    methods: {
        async showSmartLayer() {
            await this.$nextTick();

            if (this.$auth.user.isSupplier) return;

            this.$auth.setUser({
                ... this.$auth.user,
                smartShown: true
            });

            await importOrganizationsShopsFromSmart();
            let items = await fetchCompanyList({ params: { smart: true } });

            if (!items.length) return;

            const success = await this.$layer.open('SmartLayer', { items: items.map(item => ({ ...item, active: false })) });

            if (success) {
                this.$refs.listController.fetchData();
            }
        },
        fetchList(...config) {
            return fetchCompanyList(...config);
        },
        async onChangeCompanyVisibility(item) {
            try {
                await changeCompanyVisibility(item.id);
                item.visible = !item.visible;
            } catch (e) {
                console.log(e);
                processError(e);
                item.visible = !!item.visible;
            }
        },
        async onSelectCompany(companyId) {
            try {
                await selectCompany(companyId, { progress: false });
                await this.$auth.fetchUser();
                this.$router.push({ name: 'company' });
            } catch (e) {
                console.log('Unable to select company', e);
            }
        },
        onAddCompany() {
            this.isAdding = true;
            this.showCompanyJoinInn = false;
        },
        onAddCompanyCancel() {
            this.isAdding = false;
        },
        resetFormData() {
            this.formData = {
                title: '',
                inn: '',
                typeCode: ''
            };
        },
        async onAddCompanySave() {
            this.validateFormData();

            if (Object.keys(this.formErrors).length) return;

            this.addLoading = true;

            try {
                await addCompany(this.normalizedFormData);
                await this.$refs.listController.fetchData();
                this.isAdding = false;
                this.resetFormData();
            } catch (e) {
                const requestErrors = e.response?.data?.error?.request || {};
                const inn = requestErrors?.inn;

                if (inn && inn.startsWith('Такая организация уже есть в сервисе')) {
                    this.isAdding = false;
                    this.showCompanyJoinInn = true;
                    delete e.response.data.error.request.inn;
                }

                processError(e, this.formErrors);
                console.log(e);
            }

            this.addLoading = false;
        },
        async onChangeCompanyOwners(id) {
            const result = await this.$layer.open('CompanyOwnersChangeLayer', { id: id });

            if (result) this.$refs.listController.fetchData();
        },
        async companyJoinInn() {
            this.isJoiningInn = true;

            try {
                await companyJoinInn({
                    inn: this.formData.inn
                });

                await this.$layer.alert({
                    title: 'Спасибо',
                    message: 'Ваша заявка отправлена'
                });
                this.resetFormData();
                this.showCompanyJoinInn = false;
            } catch (e) {
                processError(e, this.formErrors);
            }

            this.isJoiningInn = false;
        }
    },
    breadcrumbs() {
        return [
            { title: 'Мои организации', link: { name: 'company-list' } }
        ];
    }
};
</script>
