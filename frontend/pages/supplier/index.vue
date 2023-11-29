<template>
    <section class="box__suppliers__page">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <h1>Поставщики</h1>
                </div>
                <!--
                    <div class="col-3">
                        <div class="box__select">
                            <select>
                                <option value="1-1">
                                    Товарная группа
                                </option>
                                <option value="1-2">
                                    Товарная группа - 1
                                </option>
                                <option value="1-2">
                                    Товарная группа - 2
                                </option>
                                <option value="1-2">
                                    Товарная группа - 3
                                </option>
                            </select>
                        </div>
                    </div>
                -->
                <div class="col-3">
                    <div class="box__search">
                        <form @submit.prevent="onSearch">
                            <InputField
                                v-model="formData.query"
                                placeholder="Найти поставщика"
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
                        :active-id="activeTab"
                        @select="onTabChange"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div
                        class="box__content__table"
                        :class="{ '-preloader': list.loading }"
                    >
                        <template v-if="list.items && list.items.length > 0">
                            <div class="table__content-title">
                                <div class="row">
                                    <div class="col-4">
                                        <h3>Название</h3>
                                    </div>
                                    <div :class="$auth.user.isModerator ? 'col-6' : 'col-4'">
                                        <h3>Комментарий</h3>
                                    </div>
                                    <div
                                        v-if="!$auth.user.isModerator"
                                        class="col-2"
                                    >
                                        <h3>Избранные</h3>
                                    </div>
                                    <div
                                        v-if="!$auth.user.isModerator"
                                        class="col-2"
                                    >
                                        <h3>Начать работу<br> с производителем</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="table__content">
                                <div v-for="item in list.items" :key="item.id" class="box__table__item">
                                    <div class="box__item">
                                        <div class="row">
                                            <div class="col-4">
                                                <NuxtLink :to="{ name: 'supplier-id', params: { id: item.id } }">
                                                    {{ item.title }}
                                                </NuxtLink>
                                            </div>
                                            <div :class="$auth.user.isModerator ? 'col-6' : 'col-4'">
                                                {{ item.comment }}
                                            </div>
                                            <div
                                                v-if="!$auth.user.isModerator"
                                                class="col-2"
                                            >
                                                <div class="btn__icon">
                                                    <label>
                                                        <input class="visually-hidden" type="checkbox" :checked="item.isFavorite" @change="toggleFavorite(!item.isFavorite, item.id)">
                                                        <div class="box__svg"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20.8401 4.61C20.3294 4.09901 19.7229 3.69365 19.0555 3.41709C18.388 3.14052 17.6726 2.99818 16.9501 2.99818C16.2276 2.99818 15.5122 3.14052 14.8448 3.41709C14.1773 3.69365 13.5709 4.09901 13.0601 4.61L12.0001 5.67L10.9401 4.61C9.90843 3.57831 8.50915 2.99871 7.05012 2.99871C5.59109 2.99871 4.19181 3.57831 3.16012 4.61C2.12843 5.64169 1.54883 7.04097 1.54883 8.5C1.54883 9.95903 2.12843 11.3583 3.16012 12.39L4.22012 13.45L12.0001 21.23L19.7801 13.45L20.8401 12.39C21.3511 11.8792 21.7565 11.2728 22.033 10.6054C22.3096 9.9379 22.4519 9.22249 22.4519 8.5C22.4519 7.77751 22.3096 7.06211 22.033 6.39465C21.7565 5.72719 21.3511 5.12076 20.8401 4.61V4.61Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg></div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div :class="$auth.user.isModerator && 'wraapper__button-nowrap content__justify-end'">
                                                    <div
                                                        v-if="!$auth.user.isModerator && !item.isJobRequest"
                                                        class="btn"
                                                    >
                                                        <button @click="doSendRequestJob(item)">
                                                            Начать работу
                                                        </button>
                                                    </div>
                                                    <div
                                                        v-if="$auth.user.isModerator"
                                                        class="btn btn__icon-purple"
                                                    >
                                                        <button
                                                            :disabled="authorizing"
                                                            @click="authAsModerator(item.id)"
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
                            <div v-if="list.pagination && list.pagination.pages > 1" class="table__content-bottom">
                                <div class="row">
                                    <div class="col-12">
                                        <Pagination
                                            :pages="Number(list.pagination.pages)"
                                            :page="Number(list.pagination.page)"
                                            @paginate="onPaginate"
                                        />
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div v-else>
                            Ничего не найдено
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { fetchSuppliersList, sendJobRequest } from '@/api/supplier';
import authAsModerator from '@/mixins/authAsModerator';
import clientList from '@/mixins/clientList';
import companyFavorite from '@/mixins/companyFavorite';
import Error from '@/helpers/error';

export default {
    name: 'SuppliersListPage',
    components: {},
    mixins: [
        companyFavorite,
        clientList,
        authAsModerator
    ],
    fetch() {
        return Promise.all([
            this.fetchData()
        ]);
    },
    data() {
        return {
            fetchListApi: fetchSuppliersList,
            activeTab: 'all'
        };
    },
    computed: {
        tabs() {
            const result = [{
                id: 'all',
                title: 'Все',
                active: true,
                formPropName: ''
            }];

            if (!this.$auth.user.isModerator) result.push(
                {
                    id: 'mySuppliers',
                    title: 'Мои поставщики',
                    active: false,
                    formPropName: 'mySuppliers'
                },
                {
                    id: 'favorite',
                    title: 'Избранные поставщики',
                    active: false,
                    formPropName: 'favorite'
                }
            );

            return result;
        }
    },
    methods: {
        async doSendRequestJob(item) {
            const supplierId = item.id;
            if (!supplierId) return;
            if (item.isJobRequest) return;

            const textOrSuccess = await this.$layer.open('RequestJobLayer');
            if (!textOrSuccess) return;

            try {
                await sendJobRequest(supplierId, {
                    text: typeof textOrSuccess === 'string' ? textOrSuccess : ''
                });
                this.fetchData();
            } catch (e) {
                const err = Error.normalize(e);
                if (err?.message) {
                    this.$layer.alert({
                        title: 'Ошибка',
                        message: err.message
                    });
                }
            }
        }
    }
};
</script>
