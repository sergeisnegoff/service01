<template>
    <div class="box__form-centred">
        <div
            class="wrapper-form"
            :class="{
                '-preloader': loading
            }"
        >
            <div class="row">
                <div class="col-12">
                    <h1>Восстановить пароль</h1>
                </div>
            </div>
            <template v-if="step === STEPS.PHONE">
                <div
                    :class="{
                        '-preloader': phoneConfirmLoading
                    }"
                >
                    <div class="box__form">
                        <form @submit.prevent="onVerifyCode">
                            <div class="row">
                                <div class="col-12">
                                    <InputField
                                        key="phone"
                                        v-model="formData.phone"
                                        :error="errors.phone"
                                        autocomplete="off"
                                        mask="phone"
                                        placeholder="+7 (___) ___-__-__"
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
                </div>
            </template>
            <template v-else-if="step === STEPS.CONFIRM">
                <div
                    :class="{
                        '-preloader': phoneConfirmLoading
                    }"
                >
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
                </div>
            </template>
            <template v-else-if="step === STEPS.PASSWORD">
                <div class="box__form">
                    <form @submit.prevent="onRemindPassword">
                        <div class="row">
                            <div class="col-12">
                                <InputField
                                    key="code"
                                    v-model="formData.password"
                                    :error="errors.password"
                                    type="password"
                                    class="box__input"
                                    placeholder="Введите новый пароль"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <BaseButton
                                    :disabled="!isPhoneSubmitGranted"
                                    @click="onRemindPassword"
                                >
                                    Сохранить
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
                        class="btn-link-remove"
                    >
                        Войти
                    </BaseButton>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <BaseButton
                        theme="link"
                        href="/register/"
                    >
                        Регистрация
                    </BaseButton>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
//import { emailRegex } from '@/constants/inputPatterns';
import verifyPhone from '@/mixins/verifyPhone';
import { AUTH_STEPS } from '@/constants/auth';
import { userRemindPassword } from '@/api/user';
import { AUTH_STEPS_API } from '@/constants/auth';

export default {
    name: 'AuthRemind',
    layout: 'auth',
    mixins: [verifyPhone],
    data() {
        return {
            loading: false,
            STEPS: AUTH_STEPS,
            step: AUTH_STEPS.PHONE,
            formData: {
                step: AUTH_STEPS_API.REMIND,
                password: ''
            },
            errors: {
                step: '',
                password: ''
            }
        };
    },
    computed: {
        isPhoneSubmitGranted() {
            return this.formData.phone.length === '+7 (999) 999-99-99'.length;
        }
    },
    methods: {
        async onRemindPassword() {
            try {
                const reponse = await userRemindPassword(this.formData);
                if (reponse.success) {
                    await this.$layer.alert({
                        message: 'Пароль успешно обновлен'
                    });

                    this.$router.push({ name: 'login' });
                }
            } catch (e) {
                const requestErrors = e.response?.data?.error?.request || {};
                this.errors.password = requestErrors.password || '';
                const phone = requestErrors.phone;
                if (phone) {
                    this.resetErrors();
                    this.errors.phone = phone;
                    this.step = this.STEPS.PHONE;
                }
                console.log('Unable to request password remind', e);
            }
        },
        resetErrors() {
            this.errors.step = '';
            this.errors.code = '';
        }
    }
};
</script>
