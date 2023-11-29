<template>
    <main role="main">
        <div class="box__form-centred">
            <div class="wrapper-form">
                <div class="row">
                    <div class="col-12">
                        <h1>Регистрация</h1>
                    </div>
                </div>
                <template v-if="step === STEPS.PHONE">
                    <div class="box__form">
                        <form @submit.prevent="onVerifyCode">
                            <div class="row">
                                <div class="col-12">
                                    <InputField
                                        key="phone"
                                        v-model="formData.phone"
                                        class="box__input-confirmpass"
                                        :error="errors.phone"
                                        autocomplete="off"
                                        mask="phone"
                                        placeholder="+7 (___) ___-__-__"
                                    />
                                </div>
                                <div class="col-12">
                                    <InputField
                                        key="name"
                                        v-model="formData.fullName"
                                        placeholder="ФИО"
                                        :error="errors.fullName"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <BaseButton
                                        :disabled="!isPhoneSubmitGranted"
                                        @click="onVerifyCode"
                                    >
                                        Отправить запрос
                                    </BaseButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </template>
                <template v-else-if="step === STEPS.CONFIRM">
                    <div class="row">
                        <div class="col-12">
                            <h2>Мы отправили <br>уведомление на номер <br><span>{{ formData.phone }}</span></h2>
                        </div>
                    </div>
                    <div class="box__form">
                        <form @submit.prevent="onConfirmCode">
                            <div class="row">
                                <div class="col-12">
                                    <InputField
                                        key="code"
                                        v-model="formData.code"
                                        :error="errors.code"
                                        mask=""
                                        class="box__input box__input-pass"
                                        placeholder="----"
                                        caption="Введите код из уведомления"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <BaseButton
                                        :disabled="!isPhoneSubmitGranted"
                                        @click="onConfirmCode"
                                    >
                                        Продолжить
                                    </BaseButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </template>
                <template v-else-if="step === STEPS.PASSWORD">
                    <div class="box__form">
                        <form @submit.prevent="onRegister">
                            <div class="row">
                                <div class="col-12">
                                    <InputField
                                        key="code"
                                        v-model="formData.password"
                                        :error="errors.password"
                                        type="password"
                                        class="box__input box__input-confirmpass"
                                        placeholder="Придумайте пароль"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <InputField
                                        key="code"
                                        v-model="formData.confirmPassword"
                                        :error="errors.confirmPassword"
                                        type="password"
                                        class="box__input"
                                        placeholder="Повторите пароль"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <BaseButton
                                        :disabled="!isPhoneSubmitGranted"
                                        @click="onRegister"
                                    >
                                        Продолжить
                                    </BaseButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </template>
                <div class="row">
                    <div class="col-12">
                        <BaseButton
                            theme="link"
                            href="/login/"
                        >
                            Войти
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
<script>
import { userRegister } from '@/api/user';
import processError from '@/helpers/processError';
import verifyPhone from '@/mixins/verifyPhone';
import { AUTH_STEPS } from '@/constants/auth';

export default {
    name: 'Registration',
    layout: 'auth',
    mixins: [
        verifyPhone
    ],
    data: () => ({
        STEPS: AUTH_STEPS,
        step: AUTH_STEPS.PHONE,
        formData: {
            password: '',
            confirmPassword: '',
            fullName: ''
        },
        errors: {
            password: '',
            confirmPassword: '',
            fullName: ''
        }
    }),
    computed: {
        normalizedData() {
            if (this.$route.query.invite) {
                return {
                    ...this.formData,
                    invite: this.$route.query.invite
                };
            } else {
                return this.formData;
            }
        }
    },
    methods: {
        async onRegister() {
            this.resetErrors();
            try {
                const resp = await userRegister(this.normalizedData);
                console.log('resp', resp);

                if (resp?.token) {
                    this.$auth.setUserToken(resp.token.token);
                }
                if (resp?.user) {
                    this.$auth.setUser(resp.user);
                    this.$router.push({ name: 'company-list' });
                }
            } catch (e) {
                const requestErrors = e.response?.data?.error?.request || {};
                const phone = requestErrors.phone;
                if (phone) {
                    this.resetErrors();
                    this.errors.phone = phone;
                    this.step = this.STEPS.PHONE;
                } else {
                    processError(e, this.errors);
                }
            }
        },
        resetErrors() {
            this.errors.phone = '';
            this.errors.code = '';
            this.errors.password = '';
            this.errors.confirmPassword = '';
            this.errors.fullName = '';
        }
    }
};
</script>
