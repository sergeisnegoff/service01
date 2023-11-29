<template>
    <div v-if="!company.data.loading" class="container">
        <div class="row">
            <div v-if="companyData.title" class="col-9">
                <h1>{{ companyData.title }}</h1>
            </div>
            <div
                v-if="!$auth.user.isModerator"
                class="col-3"
            >
                <div class="btn btn__icon-favorite btn-right">
                    <button @click="toggleFavorite(!companyData.isFavorite, companyData.id)">
                        <span :style="{backgroundImage: `url(${ require('@/assets/img/icon/to-favorites.svg') })`}"></span>
                        {{ companyData.isFavorite ? 'Удалить с избранного' : 'В избранное' }}
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <TabLinks :items="getNavigation" />
            </div>
        </div>
        <div class="row content-aboutcompany">
            <div v-if="companyData.galleryImages.length" class="col-12 col-xl-6">
                <Slider
                    :items="companyData.galleryImages"
                />
            </div>
            <div class="col-12 col-xl-5">
                <div v-if="isBuyer && companyData.jobRequest && companyData.jobRequest.text" class="box__form-alert">
                    <div class="icon__alert">
                        <span></span>
                    </div>
                    <div class="description-alert">
                        <p>{{ companyData.jobRequest.text }}</p>
                    </div>
                </div>
                <div class="box__company__logo">
                    <img :src="companyData.logo" alt="">
                </div>
                <div v-if="companyData.site" class="box__company-site">
                    <a target="_blank" :href="companyData.site">{{ companyData.site }}</a>
                </div>
                <div v-if="isSupplier" class="box__company-string">
                    <ul>
                        <li v-if="companyData.inn">
                            <span>ИНН: </span>{{ companyData.inn }}
                        </li>
                        <li v-if="companyData.kpp">
                            <span>КПП: </span>{{ companyData.kpp }}
                        </li>
                        <li v-if="companyData.site">
                            <span>Доп. ресурс: </span><a target="_blank" :href="companyData.site">{{ companyData.site }}</a>
                        </li>
                    </ul>
                </div>
                <div v-if="companyData.description" class="box__company-description">
                    <p v-html="companyData.description"></p>
                </div>
                <div v-if="!isSupplier && !$auth.user.isModerator" class="box__company-comment">
                    <div class="box__form" :class="{ '-preloader': commentLoading }">
                        <form @submit.prevent="onAddComment">
                            <TextareaField
                                v-model="company.data.comment"
                                placeholder="Комментарий о организации"
                            />
                            <div class="box__comment-prompt">
                                Данный комментарий будет виден только для вас
                            </div>
                            <BaseButton type="submit">
                                Отправить
                            </BaseButton>
                        </form>
                    </div>
                </div>
            </div>
            <div v-if="isSupplier && (companyData.deliveryTerm || companyData.paymentTerm)" class="col-12">
                <div class="box__tabs">
                    <ul>
                        <li v-if="companyData.deliveryTerm" :class="{ active: activeTabId === 'delivery' }" @click="changeTab('delivery')">
                            Доставка
                        </li>
                        <li v-if="companyData.paymentTerm" :class="{ active: activeTabId === 'payment' }" @click="changeTab('payment')">
                            Оплата
                        </li>
                    </ul>
                </div>
                <div v-if="companyData.deliveryTerm" class="box__tab-content" :class="{ active: activeTabId === 'delivery' }" v-html="companyData.deliveryTerm"></div>
                <div v-if="companyData.paymentTerm" class="box__tab-content" :class="{ active: activeTabId === 'payment' }" v-html="companyData.paymentTerm"></div>
            </div>
            <div v-if="isSupplier" class="col-12">
                <div class="box__company-comment">
                    <div class="box__form" :class="{ '-preloader': commentLoading }">
                        <form @submit.prevent="onAddComment">
                            <TextareaField
                                v-model="company.data.comment"
                                placeholder="Комментарий о организации"
                            />
                            <div class="box__comment-prompt">
                                Данный комментарий будет виден только для вас
                            </div>
                            <BaseButton type="submit">
                                Отправить
                            </BaseButton>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { fetchCompanyDetail, companyAddComment } from '@/api/company';
import companyFavorite from '@/mixins/companyFavorite';
import processError from '@/helpers/processError';
import { sendJobRequest } from '@/api/supplier';

export default {
    name: 'CompanyInfo',
    components: {},
    mixins: [
        companyFavorite
    ],
    props: {
        mode: {
            type: String
        }
    },
    fetch() {
        return Promise.all([
            this.fetchData()
        ]);
    },
    data() {
        return {
            activeTabId: 'delivery',
            company: {
                data: {},
                loading: false,
                cancel: null
            },
            commentLoading: false
        };
    },
    computed: {
        isSupplier() {
            return this.mode === 'supplier';
        },
        isBuyer() {
            return this.mode === 'buyer';
        },
        isModerator() {
            return this.mode === 'moderator';
        },
        getNavigation() {
            if (this.isSupplier) {
                return [
                    {
                        id: 'tab1',
                        title: 'О организации',
                        link: this.$route.name === 'supplier-id' ? null : {
                            name: 'supplier-id',
                            params: this.$route.params.id
                        },
                        active: this.$route.name === 'supplier-id'
                    },
                    {
                        id: 1,
                        title: 'Товары',
                        link: this.$route.name === 'supplier-id-goods' ? null : {
                            name: 'supplier-id-goods',
                            params: this.$route.params.id
                        },
                        active: this.$route.name === 'supplier-id-goods'
                    }
                ];
            } else if (this.isModerator) {
                return [{
                    id: 'tab1',
                    title: 'О организации',
                    link: this.$route.name === 'supplier-id' ? null : {
                        name: 'buyer-id',
                        params: this.$route.params.id
                    },
                    active: this.$route.name === 'supplier-id'
                }];
            } else {
                return [
                    {
                        id: 'tab1',
                        title: 'О организации',
                        link: this.$route.name === 'buyer-id' ? null : {
                            name: 'buyer-id',
                            params: this.$route.params.id
                        },
                        active: this.$route.name === 'buyer-id'
                    },
                    {
                        id: 1,
                        title: 'Торговые точки',
                        link: this.$route.name === 'buyer-id-organizations' ? null : {
                            name: 'buyer-id-organizations',
                            params: this.$route.params.id
                        },
                        active: this.$route.name === 'buyer-id-organizations'
                    }
                ];
            }
        },

        companyData() {
            const logo = this.company.data?.image?.file?.url;
            const galleryImages = (Array.isArray(this.company.data.gallery) ? this.company.data.gallery : []).map(image => image && image.file.url).filter(x => x);

            return {
                ...(this.company.data || {}),
                logo,
                galleryImages
            };
        }
    },
    methods: {
        async fetchData() {
            return fetchCompanyDetail(this.$route.params.id, {
                data: {
                    cancelToken: new this.$axios.CancelToken((cancel) => {
                        this.company.cancel = cancel;
                        this.company.loading = true;
                    })
                }
            })
                .then(response => {
                    this.company.data = response;
                })
                .finally(() => {
                    this.company.loading = false;
                    this.company.cancel = false;
                });
        },
        async onAddComment() {
            this.commentLoading = true;

            try {
                this.company.data = await companyAddComment(this.$route.params.id, {
                    text: this.company.data?.comment
                });

                this.$layer.alert({
                    title: 'Спасибо',
                    description: 'Данные добавлены'
                });
            } catch (e) {
                console.log('Unable to add comment', e);
            }

            this.commentLoading = false;
        },
        async doSendRequestJob() {
            if (this.company.data.isJobRequest) return;

            const text = await this.$layer.open('RequestJobLayer');
            if (!text) return;

            try {
                await sendJobRequest(this.company.data.id, {
                    text
                });
                this.fetchData();
            } catch (e) {
                processError(e);
            }
        },
        changeTab(tabId) {
            this.activeTabId = tabId;
        }
    }
};
</script>
