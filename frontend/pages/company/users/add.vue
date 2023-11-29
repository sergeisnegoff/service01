<template>
    <section class="box__buyers__page invoice-page">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1>Добавить пользователя</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-5">
                    <div class="box__form">
                        <form
                            v-if="view === VIEWS.ADD"
                            @submit.prevent="search"
                        >
                            <div class="row">
                                <div class="col-12 mt-15">
                                    <InputField
                                        v-model="formData.search"
                                        placeholder="Email или телефон"
                                        :error="formErrors.search"
                                    />
                                </div>
                                <div class="col-12 mt-15">
                                    <BaseButton
                                        type="submit"
                                        :disabled="sending"
                                    >
                                        Далее
                                    </BaseButton>
                                </div>
                            </div>
                        </form>
                        <form
                            v-else-if="view === VIEWS.FOUND"
                            @submit.prevent="addUser(false)"
                        >
                            <div class="row">
                                <div class="col-12 mt-15">
                                    {{ user.firstName }}
                                </div>
                                <div class="col-12 mt-15">
                                    {{ user.phone }}
                                </div>
                                <div class="col-12 mt-30">
                                    <div class="wraapper__button-nowrap content__justify-start">
                                        <BaseButton
                                            type="button"
                                            :disabled="sending"
                                            @click="view = VIEWS.ADD"
                                        >
                                            Назад
                                        </BaseButton>
                                        <BaseButton
                                            :disabled="sending"
                                            type="submit"
                                        >
                                            Добавить пользователя
                                        </BaseButton>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form
                            v-else-if="view === VIEWS.NOT_FOUND"
                            @submit.prevent="addUser(true)"
                        >
                            <div class="row">
                                <div class="col-12 mt-15">
                                    {{ formData.search }}
                                </div>
                                <div class="col-12 mt-15">
                                    <div class="box__form-alert">
                                        <div class="icon__alert">
                                            <span></span>
                                        </div>
                                        <div class="description-alert">
                                            <p>
                                                Пользователь в системе не найден.
                                                <br>
                                                Вы можете выслать приглашение на присоединение на указанный email или телефон
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-15">
                                    <div class="wraapper__button-nowrap content__justify-start">
                                        <div class="btn">
                                            <BaseButton
                                                type="button"
                                                :disabled="sending"
                                                @click="view = VIEWS.ADD"
                                            >
                                                Назад
                                            </BaseButton>
                                        </div>
                                        <div class="btn">
                                            <BaseButton
                                                :disabled="sending"
                                                type="submit"
                                            >
                                                Выслать приглашение
                                            </BaseButton>
                                        </div>
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
import { findUser } from '@/api/user';
import { addCompanyUser, addCompanyUserRole } from '@/api/company';
import validateMixin from '@/mixins/validateMixin';

const VIEWS = {
    ADD: 1,
    FOUND: 2,
    NOT_FOUND: 3
};

export default {
    mixins: [
        validateMixin('formData', 'formErrors', {})
    ],
    data() {
        return {
            view: VIEWS.ADD,
            VIEWS,
            isUserLoading: false,
            formData: {
                search: ''
            },
            user: null,
            formErrors: {},
            sending: false
        };
    },
    methods: {
        async addUser(invite = false) {
            const data = {
                search: this.formData.search,
                invite
            };

            if (this.user) {
                data.userId = this.user.id;
            }

            if (invite) {
                delete data.userId;
            }

            try {
                const message = invite ? 'Приглашение отправлено' : 'Успешно';

                this.user = await addCompanyUser(data);
                await this.$layer.alert({
                    message
                });
                if (this.user) {
                    await this.doAddUserRole();
                }
                this.$router.push({ name: 'company-users' });
            } catch (e) {
                const errors = e.response?.data?.error;

                this.view = VIEWS.ADD;
                this.user = null;

                this.formErrors.search = errors?.request?.phone || '';
            }
        },
        async doAddUserRole() {
            const roleData = await this.$layer.open('UserAddRoleLayer', {
                userId: this.user.id
            });

            if (!roleData) return;

            try {
                await addCompanyUserRole(this.user.id, roleData);
            } catch (e) {
                console.log(e);
            }
        },
        async search() {
            this.sending = true;

            try {
                const data = await findUser({
                    params: this.formData
                });

                this.user = data;

                this.view = VIEWS.FOUND;
            } catch (e) {
                this.view = VIEWS.NOT_FOUND;
            }

            this.sending = false;
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Пользователи',
                link: {
                    name: 'company-users'
                }
            },
            {
                title: 'Добавление'
            }
        ];
    }
};
</script>
