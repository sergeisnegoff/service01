<template>
    <section class="box__buyers__page invoice-page">
        <div class="container">
            <div class="row">
                <div class="col-7">
                    <h1>Профиль</h1>
                </div>
                <div class="col-5">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn btn-green btn__icons">
                            <button
                                :disabled="sending || user.loading"
                                @click="save"
                            >
                                <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                Сохранить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="row"
                :class="user.loading && '-preloader'"
            >
                <div class="col-5">
                    <div class="box__form">
                        <form action="">
                            <div class="row mt-30">
                                <div class="col-12">
                                    <h4>Основные</h4>
                                </div>
                                <div class="col-12">
                                    <InputField
                                        v-model="formData.firstName"
                                        placeholder="ФИО*"
                                        :error="formErrors.firstName"
                                    />
                                </div>
                                <div class="col-12">
                                    <InputField
                                        v-model="formData.email"
                                        placeholder="Email"
                                        :error="formErrors.email"
                                    />
                                </div>
                                <div class="col-12">
                                    <InputField
                                        v-model="formData.phone"
                                        mask="phone"
                                        autocomplete="tel"
                                        placeholder="Телефон*"
                                        :error="formErrors.phone"
                                    />
                                </div>
                            </div>
                            <div class="row mt-30">
                                <div class="col-12">
                                    <h4>
                                        Смена пароля
                                    </h4>
                                </div>
                                <div class="col-12">
                                    <div class="box__input">
                                        <InputField
                                            v-model="formData.oldPassword"
                                            type="password"
                                            placeholder="Старый пароль"
                                            :error="formErrors.oldPassword"
                                        />
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="box__input">
                                        <InputField
                                            v-model="formData.password"
                                            type="password"
                                            placeholder="Новый пароль"
                                            :error="formErrors.password"
                                        />
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="box__input">
                                        <InputField
                                            v-model="formData.confirmPassword"
                                            type="password"
                                            placeholder="Повторите пароль"
                                            :error="formErrors.confirmPassword"
                                        />
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import validateMixin from '@/mixins/validateMixin';
import processError from '@/helpers/processError';
import { fetchUser, updateUser } from '@/api/user';

export default {
    mixins: [
        validateMixin('formData', 'formErrors', {})
    ],
    fetch() {
        return this.fetchData()
            .then(() => this.fillFormData());
    },
    data() {
        return {
            user: {
                data: null,
                loading: true
            },
            formData: {},
            formErrors: {},
            sending: false
        };
    },
    methods: {
        async fetchData() {
            const data = await fetchUser();

            this.user = { data, pending: false };
        },
        async save() {
            this.sending = true;

            try {
                await updateUser(this.formData);
                await this.$layer.alert({
                    message: 'Успешно'
                });

                await this.redirectToLogin();
            } catch (e) {
                processError(e, this.formErrors);
            }

            this.sending = false;
        },
        async redirectToLogin() {
            const user = this.user.data;

            if (this.formData.password || user.email !== this.formData.email || user.phone !== this.formData.phone.replace(/[^0-9]/gui, '')) {
                await this.$auth.logout();
                this.$router.push({ name: 'login' });
            }
        },
        fillFormData() {
            this.formData = {
                firstName: this.user.data.firstName,
                email: this.user.data.email,
                phone: this.user.data.phone,
                oldPassword: '',
                password: '',
                confirmPassword: ''
            };
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Профиль'
            }
        ];
    }
};
</script>
