<template>
    <BaseLayer>
        <template v-slot:close>
            <a href="#" @click.prevent="$emit('close')">
                <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 28.2843L28.2843 3.09944e-05L30.4056 2.12135L2.12132 30.4056L0 28.2843Z" />
                    <path d="M2.12134 0L30.4056 28.2843L28.2843 30.4056L1.75834e-05 2.12132L2.12134 0Z" />
                </svg>
            </a>
        </template>
        <template v-slot:main>
            <div class="py-5 px-12 text-center">
                <div class="popup__content">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="margin-15">
                                Смена владельца
                            </h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="box__form">
                                <form @submit.prevent="changeCompanyOwners">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="box__description text-lg">
                                                После смены владельца <br> вы не сможете снова войти в организацию
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 margin-15">
                                            <InputField
                                                v-model="formData.phone"
                                                :error="formErrors.phone && ' '"
                                                autocomplete="off"
                                                mask="phone"
                                                placeholder="+7 (___) ___-__-__"
                                                class="box__input box__input-confirmpass"
                                            />
                                            <label
                                                v-if="formErrors.phone"
                                                class="invalid-feedback"
                                            >
                                                {{ formErrors.phone }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="btn">
                                                <BaseButton
                                                    :disabled="sending"
                                                    type="submit"
                                                >
                                                    Сменить владельца
                                                </BaseButton>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </BaseLayer>
</template>
<script>
import { changeCompanyOwners } from '@/api/company';

import processError from '@/helpers/processError';
import validateMixin from '@/mixins/validateMixin';


export default {
    mixins: [
        validateMixin('formData', 'formErrors', {})
    ],
    props: {
        id: { type: [String, Number] }
    },
    data() {
        return {
            formData: {
                phone: ''
            },
            formErrors: {},
            sending: false
        };
    },
    computed: {
    },
    methods: {
        async changeCompanyOwners() {
            this.sending = true;

            try {
                await changeCompanyOwners(this.id, this.formData);
                this.$emit('close', true);
            } catch (e) {
                processError(e, this.formErrors);
            }

            this.sending = false;
        }
    }
};
</script>
