<template>
    <div class="box__form-centred">
        <div class="wrapper-form">
            <div class="row">
                <div class="col-12">
                    <h1>Войти</h1>
                </div>
            </div>
            <template v-if="step === STEPS.CREDENTIALS">
                <div class="box__form">
                    <form @submit.prevent="doAuthorize">
                        <div class="row">
                            <div class="col-12">
                                <InputField
                                    key="phone"
                                    v-model="formData.phone"
                                    :error="errors.phone"
                                    mask="phone"
                                    autocomplete="tel"
                                    placeholder="+7 (___) ___-__-__"
                                    class="box__input box__input-confirmpass"
                                />
                            </div>
                            <div class="col-12">
                                <InputField
                                    key="password"
                                    v-model="formData.password"
                                    type="password"
                                    :error="errors.password"
                                    autocomplete="off"
                                    class="box__input"
                                    placeholder="Пароль"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <BaseButton
                                    @click="doAuthorize"
                                >
                                    Продолжить
                                </BaseButton>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-12">
                        <BaseButton
                            theme="link"
                            href="/remind/"
                            class="btn-link-remove"
                        >
                            Восстановить пароль
                        </BaseButton>
                    </div>
                    <div class="col-12">
                        <BaseButton
                            theme="link"
                            href="/register/"
                        >
                            Регистрация
                        </BaseButton>
                    </div>
                </div>
            </template>
            <template v-else-if="step === STEPS.CONFIRM">
                <div class="row">
                    <div class="col-12">
                        <h2>Мы отправили <br> смс на номер <br><span>{{ formData.phone }}</span></h2>
                    </div>
                </div>
                <div class="box__form">
                    <form @submit.prevent="doAuthorize">
                        <div class="row">
                            <div class="col-12">
                                <InputField
                                    key="code"
                                    v-model="formData.code"
                                    :error="errors.code"
                                    mask=""
                                    class="box__input box__input-pass"
                                    placeholder="----"
                                    caption="Введите код из смс"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <BaseButton
                                    @click="doAuthorize"
                                >
                                    Продолжить
                                </BaseButton>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-12">
                        <BaseButton
                            theme="link"
                            @click="resendCode"
                        >
                            Выслать повторно код
                        </BaseButton>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
<script>
import { requestVerification } from '@/api/verification';
import { userAuthorize } from '@/api/user';
import { AUTH_STEPS_API } from '@/constants/auth';

export default {
    layout: 'auth',
    name: 'LoginPage',
    components: {},
    data() {
        return {
            STEPS: {
                CREDENTIALS: 'CREDENTIALS',
                CONFIRM: 'CONFIRM'
            },
            step: 'CREDENTIALS',
            isPhoneConfirmed: false,
            formData: {
                phone: '',
                password: '',
                code: '',
                step: 'one'
            },
            errors: {
                phone: '',
                password: '',
                code: ''
            }
        };
    },
    computed: {
        redirectRoute() {
            const isModerator = this.$auth.user.isModerator;

            return isModerator ? { name: 'requests' } : { name: 'company-list' };
        }
    },
    created() {
        this.$cookies.remove('authData');
    },
    methods: {
        async doAuthorize() {
            this.resetErrors();

            try {
                if (this.formData.step === AUTH_STEPS_API.CHECK) {
                    const response = await userAuthorize({
                        ...(this.formData || {})
                    });

                    if (response.success) {
                        await this.onRequestVerification();
                    }

                    return;
                }

                const response = await this.$auth.loginWith('local', {
                    data: this.formData
                });

                if (!response.data?.verified) {
                    this.errors.code = 'Неверный код';

                    return;
                }

                if (response?.data?.user) {
                    this.$auth.setUser(response.data.user);
                    this.welcome();
                }
            } catch (e) {
                const errors = e.response?.data?.error;
                this.errors.phone = errors?.request?.phone || '';
                this.errors.password = errors?.request?.password || '';
                console.log('Unable to check user', e);
            }
        },
        async onRequestVerification() {
            return requestVerification('phone', {
                key: this.formData.phone
            })
                .then((response) => {
                    this.formData.step = AUTH_STEPS_API.AUTH;
                    this.step = this.STEPS.CONFIRM;
                    if (response.code) {
                        this.formData.code = response.code;
                    }
                })
                .catch(e => {
                    const error = e.response?.data?.error || {};
                    const requestErrors = error.request || {};
                    this.errors.phone = requestErrors.phone;
                    this.errors.password = requestErrors.password;
                    this.errors.code = requestErrors.code;

                    if (error.message) {
                        this.errors.phone = this.errors.code = error.message;
                    }
                    console.log('Unable to request verification code', e);
                });
        },
        welcome() {
            if (this.redirectRoute) {
                this.$router.push(this.redirectRoute);
            }
        },
        resendCode() {
            this.resetErrors();
            this.onRequestVerification();
        },
        resetErrors() {
            this.errors.phone = '';
            this.errors.password = '';
            this.errors.code = '';
        }
    }
};
</script>
