<template>
    <BaseLayer class="layer_right">
        <template v-slot:close>
            <a href="#" @click.prevent="$emit('close')">
                <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 28.2843L28.2843 3.09944e-05L30.4056 2.12135L2.12132 30.4056L0 28.2843Z" />
                    <path d="M2.12134 0L30.4056 28.2843L28.2843 30.4056L1.75834e-05 2.12132L2.12134 0Z" />
                </svg>
            </a>
        </template>
        <template v-slot:main>
            <div class="p-5">
                <div class="popup__content">
                    <div class="row">
                        <div class="col-12">
                            <h3>Фильтрация</h3>
                        </div>
                    </div>
                    <div class="box__form">
                        <form>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>
                                        Внешний код / ID накладной
                                    </h4>
                                </div>
                                <div class="col-12">
                                    <InputField
                                        v-model="formDataLocal.search"
                                        placeholder="Внешний код / ID накладной"
                                    />
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Дата</h4>
                                </div>
                                <div class="col-6">
                                    <div class="box__input">
                                        <span class="input__icon" :style="{ backgroundImage: `url(${require('@/assets/img/icon/calendar.svg')})` }"></span>
                                        <DatePicker
                                            v-model="formDataLocal.dateFrom"
                                            placeholder="От"
                                        />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="box__input">
                                        <span class="input__icon" :style="{ backgroundImage: `url(${require('@/assets/img/icon/calendar.svg')})` }"></span>
                                        <DatePicker
                                            v-model="formDataLocal.dateTo"
                                            placeholder="До"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>
                                        {{ $auth.user.isSupplier ? 'Покупатель' : 'Поставщик' }}
                                    </h4>
                                </div>
                                <div class="col-12">
                                    <SelectField
                                        v-model="formDataLocal.companyId"
                                        :class="($auth.user.isSupplier ? buyers.loading : suppliers.loading) && '-preloader'"
                                        :options="($auth.user.isSupplier ? buyers.data : suppliers.data)"
                                        track-by="id"
                                        placeholder="Поставщик"
                                        label="title"
                                    />
                                </div>
                            </div>
                            <div
                                v-if="$auth.user.isBuyer"
                                class="row margin-15"
                            >
                                <div class="col-12">
                                    <h4>Торговая точка</h4>
                                </div>
                                <div class="col-12">
                                    <SelectField
                                        v-model="formDataLocal.shopId"
                                        :class="shopsLocal.loading && '-preloader'"
                                        :options="shopsLocal.data"
                                        track-by="id"
                                        placeholder="Торговая точка"
                                        label="title"
                                    />
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Сумма</h4>
                                </div>
                                <div class="col-6">
                                    <InputField
                                        v-model="formDataLocal.priceFrom"
                                        placeholder="От"
                                    />
                                </div>
                                <div class="col-6">
                                    <InputField
                                        v-model="formDataLocal.priceTo"
                                        placeholder="До"
                                    />
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Статус принятия накладной</h4>
                                </div>
                                <div class="col-12">
                                    <SelectField
                                        v-model="formDataLocal.acceptanceStatusId"
                                        :options="acceptanceStatuses.data"
                                        track-by="id"
                                        placeholder="Статус принятия накладной"
                                        label="title"
                                    />
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-6">
                                    <div class="btn">
                                        <button @click.prevent="submit">
                                            Применить
                                        </button>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="btn-whitepurple text-right">
                                        <button @click.prevent="reset">
                                            Сбросить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </BaseLayer>
</template>

<script>
import { cloneDeep } from 'lodash';

export default {
    props: {
        formData: { type: Object, default: () => ({}) },
        acceptanceStatuses: { type: Object, default: () => ({}) },
        shops: { type: Object, default: () => ({}) },
        suppliers: { type: Object, default: () => ({}) },
        buyers: { type: Object, default: () => ({}) },
        onSubmit: { type: Function, default: () => {} },
        onReset: { type: Function, default: () => {} },
        onOrganizationChange: { type: Function, default: () => {} }
    },
    data() {
        return {
            formDataLocal: cloneDeep(this.formData),
            shopsLocal: cloneDeep(this.shops)
        };
    },
    methods: {
        reset() {
            this.formDataLocal = {};
        },
        submit() {
            this.onSubmit(this.formDataLocal);
            this.$emit('close');
        }
    }
};
</script>
