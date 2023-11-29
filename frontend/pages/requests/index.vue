<template>
    <section class="box__moderator__page">
        <div class="container">
            <div class="row">
                <div class="col-9">
                    <h1>Заявки</h1>
                </div>
                <div class="col-3">
                    <div class="box__search">
                        <form @submit.prevent="onSearch">
                            <InputField
                                v-model="formData.query"
                                placeholder="Найти покупателя"
                            />
                            <BaseButton
                                class="btn__search"
                                type="submit"
                            />
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <TabLinks
                        :items="tabs"
                        @select="onTabChange"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div
                        class="box__content__table"
                        :class="{
                            '-preloader': list.loading
                        }"
                    >
                        <template v-if="requestList.length > 0">
                            <div class="table__content-title">
                                <div class="row">
                                    <div class="col-1">
                                        <h3>№</h3>
                                    </div>
                                    <div class="col-4">
                                        <h3>Название</h3>
                                    </div>
                                    <div class="col-3">
                                        <h3>Дата заявки</h3>
                                    </div>
                                    <div class="col-4">
                                        <h3>Действия</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="table__content">
                                <div
                                    v-for="(item, itemIndex) in requestList"
                                    :key="item.id"
                                    class="box__table__item"
                                >
                                    <div class="box__item">
                                        <div class="row">
                                            <div class="col-1">
                                                {{ itemIndex + 1 + ((list.pagination.page - 1) * list.limit) }}
                                            </div>
                                            <div class="col-4">
                                                <NuxtLink
                                                    v-if="item.company && item.company.id"
                                                    :to="{ name: 'supplier-id', params: { id: item.company.id } }"
                                                >
                                                    {{ item.company.title || ' - ' }}
                                                </NuxtLink>
                                                <span v-else>
                                                    {{ item.company.title || ' - ' }}
                                                </span>
                                            </div>
                                            <div class="col-3">
                                                {{ item.datePretty }}
                                            </div>
                                            <div class="col-4">
                                                <div
                                                    v-if="item.actions.length"
                                                    :class="{
                                                        'wraapper__button-nowrap': true,
                                                        '-preloader -preloader_top': item.loading
                                                    }"
                                                >
                                                    <BaseButton
                                                        v-for="action in item.actions"
                                                        :key="action.id"
                                                        :class="action.className"
                                                        @click="onChangeRequestStatus(item, action.status)"
                                                    >
                                                        {{ action.title }}
                                                    </BaseButton>
                                                    <div class="btn btn__icon-purple">
                                                        <button
                                                            v-if="$auth.user.isModerator"
                                                            :disabled="authorizing"
                                                            @click="authAsModerator(item.company.id)"
                                                        >
                                                            <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-user.svg') })` }"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div v-else>
                            Ничего не найдено
                        </div>
                        <div class="table__content-bottom">
                            <div class="row">
                                <div class="col-12">
                                    <Pagination
                                        v-if="list.pagination.pages"
                                        :pages="Number(list.pagination.pages)"
                                        :page="Number(list.pagination.page)"
                                        @paginate="onPaginate"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { REQUEST_STATUSES } from '@/constants/requests';
import { fetchRequestsList, changeRequestsStatus } from '@/api/request';
import formatDate from '@/helpers/formatDate';
import authAsModerator from '@/mixins/authAsModerator';

export default {
    name: 'VerificationRequestsList',
    mixins: [authAsModerator],
    middleware: [({ $auth, redirect }) => {
        if (!$auth.user.isModerator) {
            redirect({ name: 'index' });
        }
    }],
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
                newCount: 0,
                loading: false,
                cancel: null,
                limit: 5
            },
            changeStatus: {
                cancel: null,
                loading: false
            },
            formData: {
                query: '',
                statusCode: 'all'
            }
        };
    },
    computed: {
        tabs() {
            return [
                {
                    id: 'all',
                    title: `Все ${ this.list.newCount ? `<span>${ this.list.newCount }</span>` : '' }`,
                    active: this.formData.statusCode === 'all'
                },
                {
                    id: REQUEST_STATUSES.CONFIRMED,
                    title: 'Подтвержденные',
                    active: this.formData.statusCode === REQUEST_STATUSES.CONFIRMED
                },
                {
                    id: REQUEST_STATUSES.NEW,
                    title: 'Неподтверждённые',
                    active: this.formData.statusCode === REQUEST_STATUSES.NEW
                },
                {
                    id: REQUEST_STATUSES.BLACK_LIST,
                    title: 'Черный список',
                    active: this.formData.statusCode === REQUEST_STATUSES.BLACK_LIST
                },
                {
                    id: REQUEST_STATUSES.FAILED,
                    title: 'Отказано',
                    active: this.formData.statusCode === REQUEST_STATUSES.FAILED
                }
            ];
        },
        requestList() {
            return (Array.isArray(this.list.items) ? this.list.items : []).map(request => {
                const datePretty = formatDate(request.createdAt, 'DD MMMM YYYY в HH:mm');

                const actions = this.getRequestStatusActions(request.status.code);

                return {
                    ...request,
                    datePretty,
                    actions
                };
            });
        }
    },
    methods: {
        onTabChange(tabItem) {
            if (!tabItem || tabItem.active) return;

            this.formData.statusCode = tabItem.id;
            this.formData.query = '';
            this.fetchData();
        },
        async fetchData() {
            const page = this.list.pagination && this.list.pagination.page || 1;
            const { limit } = this.list;

            return fetchRequestsList({
                cancelToken: new this.$axios.CancelToken((cancel) => {
                    this.list.cancel = cancel;
                    this.list.loading = true;
                }),
                progress: false,
                params: {
                    ...this.formData,
                    page,
                    limit
                }
            })
                .then(response => {
                    this.list.items = (Array.isArray(response.items) ? response.items : []).map(x => x && { ...x, loading: false });
                    this.list.pagination = response.pagination;
                    this.list.newCount = response.newCount;
                })
                .finally(() => {
                    this.list.cancel = null;
                    this.list.loading = false;
                });
        },
        onSearch() {
            this.list.pagination.page = 1;
            this.fetchData();
        },
        onPaginate(page) {
            this.list.pagination.page = page;
            this.fetchData();
        },
        async onChangeRequestStatus(item, status) {
            if (!item || !status) return;

            let answer;

            if (status === REQUEST_STATUSES.FAILED) {
                answer = await this.$layer.open('RequestRejectReason');

                if (!answer) return;
            }

            const id = item.id;
            const foundItem = (Array.isArray(this.list.items) ? this.list.items : []).find(item => String(item.id) === String(id)) || {};
            foundItem.loading = true;

            try {
                await this.changeRequestStatus(id, {
                    statusCode: status,
                    answer
                });
                await this.fetchData();
            } catch (e) {
                console.log('Unable to change request status', e);
            }

            foundItem.loading = false;
        },
        async changeRequestStatus(id, payload = {}) {
            if (!id) return;

            return changeRequestsStatus(id, payload)
                .finally(() => {
                    this.changeStatus.cancel = null;
                    this.changeStatus.loading = false;
                });
        },
        getRequestStatusActions(statusCode) {
            const actions = [];

            if (statusCode === REQUEST_STATUSES.NEW) {
                actions.push({
                    id: 'actionConfirm',
                    status: REQUEST_STATUSES.CONFIRMED,
                    title: 'Принять',
                    className: 'btn-green'
                });
                actions.push({
                    id: 'actionReject',
                    status: REQUEST_STATUSES.FAILED,
                    title: 'Отказать',
                    className: 'btn-red'
                });
                actions.push({
                    id: 'actionBan',
                    status: REQUEST_STATUSES.BLACK_LIST,
                    title: 'Заблокировать',
                    className: ''
                });
            } else if (statusCode === REQUEST_STATUSES.CONFIRMED) {
                actions.push({
                    id: 'actionFailed',
                    status: REQUEST_STATUSES.FAILED,
                    title: 'Приостановить работу',
                    className: 'btn-red'
                });
                actions.push({
                    id: 'actionBan',
                    status: REQUEST_STATUSES.BLACK_LIST,
                    title: 'Заблокировать',
                    className: ''
                });
            } else if (statusCode === REQUEST_STATUSES.BLACK_LIST) {
                actions.push({
                    id: 'actionNew',
                    status: REQUEST_STATUSES.NEW,
                    title: 'Возобновить работу',
                    className: ''
                });
            } else if (statusCode === REQUEST_STATUSES.FAILED) {
                actions.push({
                    id: 'actionNew',
                    status: REQUEST_STATUSES.NEW,
                    title: 'Возобновить работу',
                    className: ''
                });
            }

            return actions;
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Заявки'
            }
        ];
    }
};
</script>
