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
                        Организации с таким ИНН уже существуют, отправить запрос на присоединение?
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
                                    <button :disabled="!formData.companyIds.length">
                                        Отправить
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
import { companyJoinInn } from '@/api/company';
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
                companyIds: []
            },
            sending: false
        };
    },
    methods: {
        async submit() {
            this.sending = true;

            try {
                await companyJoinInn({
                    inn: this.formData.companyIds.map(id => this.items.find(item => item.id === id).inn)
                });
                this.$emit('close', true);
            } catch (e) {
                this.sending = false;

                processError(e);
            }

            this.sending = false;
        }
    }
};
</script>
