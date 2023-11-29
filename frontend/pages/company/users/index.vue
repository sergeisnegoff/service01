<template>
    <section class="box__suppliers__page">
        <div class="container">
            <div class="row">
                <div class="col-9">
                    <h1>Моя организация</h1>
                </div>
                <div class="col-3">
                    <BaseButton
                        :href="{ name: 'company-users-add' }"
                    >
                        Добавить пользователя
                    </BaseButton>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <CompanyNavigation />
                </div>
            </div>
            <div class="row">
                <div
                    class="col-12"
                    :class="{
                        '-preloader': list.loading
                    }"
                >
                    <div v-if="usersList.length" class="box__content__table">
                        <div class="table__content-title">
                            <div class="row">
                                <div class="col-3">
                                    <h3>ФИО</h3>
                                </div>
                                <div class="col-2">
                                    <h3>Телефон</h3>
                                </div>
                                <div class="col-2">
                                    <h3>Почта</h3>
                                </div>
                                <div class="col-2">
                                    <h3>Регистрация</h3>
                                </div>
                                <div class="col-2">
                                    <h3>
                                        Активность
                                    </h3>
                                </div>
                                <div class="col-1">
                                    <h3>
                                        Действия
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="table__content">
                            <div
                                v-for="user in usersList"
                                :key="user.id"
                                class="box__table__item"
                                :class="{
                                    '-preloader': user.isLoading
                                }"
                            >
                                <div class="box__item">
                                    <div class="row">
                                        <div class="col-3">
                                            {{ user.firstName }}
                                        </div>
                                        <div class="col-2">
                                            {{ user.phone }}
                                        </div>
                                        <div class="col-2">
                                            {{ user.email }}
                                        </div>
                                        <div class="col-2">
                                            <div
                                                class="box__check"
                                                :class="user.register && '-checked'"
                                            >
                                                <span></span>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <InputSwitch
                                                :value="user.active"
                                                @change="onActivityChange(user)"
                                            />
                                        </div>
                                        <div class="col-1">
                                            <div class="wrapper__buttons content__justify-start">
                                                <div class="btn btn__icon-purple">
                                                    <button @click.prevent="editUser(user.id)">
                                                        <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span>
                                                    </button>
                                                </div>
                                                <div class="btn-whitepurple btn__icon-whitepurple">
                                                    <button @click="onUserRemove(user.id)">
                                                        <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table__content-bottom">
                            <div class="row">
                                <div class="col-12">
                                    <Pagination
                                        :page="Number(list.pagination.page)"
                                        :pages="Number(list.pagination.pages)"
                                        @paginate="onPaginate"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="!list.loading">
                        Ничего не найдено
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { addCompanyUserRole, fetchCompanyUsers, removeCompanyUsers, setCompanyUserActivity } from '@/api/company';

export default {
    name: 'CompanyUsers',
    fetch() {
        return Promise.all([
            this.fetchData()
        ]);
    },
    data() {
        return {
            list: {
                items: [],
                pagination: {
                    page: 1,
                    pages: 0
                },
                limit: 7,
                loading: false,
                cancel: null
            }
        };
    },
    computed: {
        usersList() {
            return (Array.isArray(this.list.items) ? this.list.items : []).map(user => user);
        }
    },
    methods: {
        async fetchData({ preloader = true } = {}) {
            const page = this.list.pagination.page || 1;
            const { limit } = this.list;

            try {
                return fetchCompanyUsers({
                    cancelToken: new this.$axios.CancelToken((cancel) => {
                        this.list.cancel = cancel;
                        if (preloader) {
                            this.list.loading = true;
                        }
                    }),
                    params: {
                        page,
                        limit
                    },
                    progress: false
                })
                    .then(response => {
                        this.list.items = (Array.isArray(response.items) ? response.items : []).map(item => ({
                            ...item,
                            isLoading: false
                        }));
                        this.list.pagination = response.pagination;
                    })
                    .finally(() => {
                        this.list.cancel = null;
                        this.list.loading = false;
                    });
            } catch (e) {
                console.log('Unable to fetch company users', e);
            }
        },
        onPaginate(page) {
            this.list.pagination.page = page;
            this.fetchData();
        },
        async onUserRemove(userId) {
            const confirm = await this.$layer.confirm({
                message: 'Вы уверены что хотите удалить пользователя?'
            });

            if (confirm) {
                await this.removeUser(userId);
                await this.fetchData();
            }
        },
        async removeUser(userId) {
            try {
                return removeCompanyUsers(userId);
            } catch (e) {
                console.log('Unable to remove company user', e);
            }
        },
        async editUser(userId) {
            if (!userId) return;

            const roleData = await this.$layer.open('UserAddRoleLayer', {
                userId
            });
            if (!roleData) return;

            this.doAddUserRole(userId, roleData);
        },
        async doAddUserRole(userId, roleData) {
            try {
                await addCompanyUserRole(userId, roleData);
            } catch (e) {
                console.log(e);
            }
        },
        async onActivityChange(user) {
            console.log(user);

            const userId = user?.id;
            if (!userId) return;
            user.isLoading = true;

            try {
                await setCompanyUserActivity(userId, { progress: false });
                await this.fetchData({ preloader: false });
            } catch (e) {
                console.log(e);
            }
            user.isLoading = false;
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Пользователи'
            }
        ];
    }
};
</script>
