<template>
    <BaseLayer width="big">
        <template v-slot:close>
            <a href="#" @click.prevent="$emit('close')">
                <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 28.2843L28.2843 3.09944e-05L30.4056 2.12135L2.12132 30.4056L0 28.2843Z" />
                    <path d="M2.12134 0L30.4056 28.2843L28.2843 30.4056L1.75834e-05 2.12132L2.12134 0Z" />
                </svg>
            </a>
        </template>
        <template v-slot:main>
            <div
                class="px-16 py-14"
                :class="sending && '-preloader'"
            >
                <form
                    class="px-1"
                    @submit.prevent="submit"
                >
                    <h2 class="text-center">
                        Относятся ли данные организации к вам?
                    </h2>
                    <div class="box__form">
                        <div class="row">
                            <div class="col-12">
                                <div class="box__content__table">
                                    <div class="table__content">
                                        <div
                                            v-for="item in items"
                                            :key="item.id"
                                            class="box__table__item"
                                        >
                                            <div
                                                class="box__item"
                                                data-table
                                                :class="item.active && 'active'"
                                            >
                                                <div class="row">
                                                    <div class="col-1">
                                                        <div class="box__checkbox">
                                                            <div class="wrapper-checkbox">
                                                                <label>
                                                                    <input
                                                                        v-model="formData.companyIds"
                                                                        :value="item.id"
                                                                        type="checkbox"
                                                                    >
                                                                    <span>
                                                                        <span class="box__checkbox-icon"></span>
                                                                        <span class="box__checkbox-text"></span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        {{ item.title }}
                                                    </div>
                                                    <div class="col-7">
                                                        ИНН: {{ item.inn }}
                                                    </div>
                                                </div>
                                                <div
                                                    class="btn-toggle"
                                                    @click="item.active = !item.active"
                                                >
                                                    <button type="button">
                                                        <span></span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div
                                                v-if="item.active"
                                                class="box__item-content"
                                            >
                                                <div class="box__content__children">
                                                    <div class="box__children-title">
                                                        <div class="row">
                                                            <div class="col-1"></div>
                                                            <div class="col-4">
                                                                <h4>Название</h4>
                                                            </div>
                                                            <div class="col-4">
                                                                <h4>Адрес</h4>
                                                            </div>
                                                            <div class="col-3">
                                                                <h4>Координаты</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        v-for="shop in item.shops"
                                                        :key="shop.id"
                                                        class="box__children-content"
                                                    >
                                                        <div class="row">
                                                            <div class="col-1">
                                                                <div class="box__checkbox">
                                                                    <div class="wrapper-checkbox">
                                                                        <label>
                                                                            <input
                                                                                v-model="formData.shopIds"
                                                                                :value="shop.id"
                                                                                type="checkbox"
                                                                            >
                                                                            <span>
                                                                                <span class="box__checkbox-icon"></span>
                                                                                <span class="box__checkbox-text"></span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                {{ shop.title }}
                                                            </div>
                                                            <div class="col-4">
                                                                {{ shop.address }}
                                                            </div>
                                                            <div class="col-3">
                                                                {{ shop.latitude }},  {{ shop.longitude }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 flex justify-center">
                                <div class="btn margin-15">
                                    <button class="mr-2" @click.prevent="$emit('close')">
                                        Закрыть
                                    </button>
                                </div>
                                <div class="btn margin-15">
                                    <button>
                                        Сохранить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </template>
    </BaseLayer>
</template>

<script>
import { approveOrganizationsShopsFromSmart } from '@/api/buyer';
import processError from '@/helpers/processError';

export default {
    name: 'RequestJobLayer',
    props: {
        items: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            formData: {
                shopIds: [],
                companyIds: []
            },
            sending: false
        };
    },
    watch: {
        'formData.companyIds'(val) {
            val.forEach(companyId => {
                const company = this.items.find(item => item.id === companyId);

                if (company.shops) {
                    company.shops.forEach(item => {
                        if (!this.formData.shopIds.includes(item.id)) {
                            this.formData.shopIds.push(item.id);
                        }
                    });
                }
            });
        }
    },
    methods: {
        async submit() {
            this.sending = true;

            try {
                let data = await approveOrganizationsShopsFromSmart({
                    companies: this.formData.companyIds.map(id => ({ companyId: id, type: 2 })),
                    shopIds: this.formData.shopIds
                });

                if (data.existCompanies?.length) {
                    await this.$layer.open('JoinOrganizationsLayer', { items: this.items.filter(item => data.existCompanies.includes(item.inn.toString())) });
                }
                this.$emit('close', true);
            } catch (e) {
                const request = e.response?.data?.error?.request;

                this.sending = false;

                if (request.companies) {
                    this.$layer.alert({
                        message: 'Ошибка',
                        description: 'Выберите организации',
                        type: 'error'
                    });

                    return;
                } else if (request.shopIds) {
                    this.$layer.alert({
                        message: 'Ошибка',
                        description: 'Выберите торговые точки',
                        type: 'error'
                    });

                    return;
                } else {
                    processError(e);
                }
            }

            this.sending = false;
        }
    }
};
</script>
