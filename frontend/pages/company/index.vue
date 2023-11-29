<template>
    <section class="box__companyuser__page">
        <div class="container">
            <div class="row">
                <div class="col-9">
                    <h1>Моя организация</h1>
                </div>
                <div class="col-3">
                    <BaseButton clas="text-right" @click="onSend">
                        Сохранить
                    </BaseButton>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <CompanyNavigation />
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div
                        class="box__form box__form-company"
                        :class="{
                            '-preloader -preloader_top': loading
                        }"
                    >
                        <form @submit.prevent="onSend">
                            <div class="row">
                                <div class="col-5">
                                    <h3>Логотип организации</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5">
                                    <FileField
                                        v-model="logoModel"
                                        :error="errors.imageId"
                                        class="input-logo"
                                        @removedItem="onRemoveLogo"
                                    />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5">
                                    <h3>Основные</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5">
                                    <InputField
                                        v-model="company.title"
                                        :error="errors.title"
                                        placeholder="Название организации*"
                                    />
                                    <InputField
                                        v-model="company.inn"
                                        :error="errors.inn"
                                        placeholder="ИНН*"
                                    />
                                    <InputField
                                        v-model="company.email"
                                        :error="errors.email"
                                        placeholder="Email"
                                    />
                                    <div v-if="errors.inn && errors.inn.startsWith('Организация с таким ИНН уже есть')">
                                        <BaseButton @click="$layer.open('FeedbackLayer')">
                                            Написать
                                        </BaseButton>
                                    </div>
                                    <template v-if="isSupplier">
                                        <InputField
                                            v-model="company.kpp"
                                            :error="errors.kpp"
                                            placeholder="КПП"
                                        />
                                        <InputField
                                            v-model="company.diadocExternalCode"
                                            :error="errors.diadocExternalCode"
                                            placeholder="ID ящика в Диадок"
                                        />
                                        <InputField
                                            v-model="company.docrobotExternalCode"
                                            :error="errors.docrobotExternalCode"
                                            placeholder="GLN в Е-Ком"
                                        />
                                        <InputField
                                            v-model="company.storehouseExternalCode"
                                            :error="errors.storehouseExternalCode"
                                            placeholder="ID в StoreHouse"
                                        />
                                        <InputField
                                            v-model="company.veterinaryEmail"
                                            :error="errors.veterinaryEmail"
                                            placeholder="Email ветеринарного врача"
                                        />
                                    </template>
                                    <InputField
                                        v-model="company.site"
                                        :error="errors.site"
                                        placeholder="Сайт"
                                    />
                                    <TextareaField
                                        v-model="company.description"
                                        :error="errors.description"
                                        placeholder="Описание организации"
                                    />
                                </div>
                                <div v-if="isSupplier" class="col-5 offset-1">
                                    <div
                                        class="box__form-alert"
                                        :class="verificationRequestContent.boxClasses"
                                    >
                                        <div class="icon__alert">
                                            <span></span>
                                        </div>
                                        <h4>Статус моей организации <span class="lowercase">{{ verificationRequestContent.status }}</span></h4>
                                        <div
                                            class="description-alert"
                                            v-html="verificationRequestContent.text"
                                        >
                                        </div>
                                        <BaseButton
                                            v-if="verificationRequestContent.canSendVerificationRequest"
                                            :disabled="!company.canSendVerificationRequest"
                                            @click="onSendRequest"
                                        >
                                            {{ verificationRequestContent.actionTitle }}
                                        </BaseButton>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5">
                                    <h3>Галерея</h3>
                                    <FileField
                                        v-model="galleryModel"
                                        :error="errors.gallery"
                                        :multiple="true"
                                        class="input-gallery"
                                        @removedItem="onRemoveGalleryItem"
                                    />
                                </div>
                            </div>
                            <div v-if="isSupplier" class="row">
                                <div class="col-5">
                                    <h3>Доставка</h3>
                                    <TextareaField
                                        v-model="company.deliveryTerm"
                                        :error="errors.deliveryTerm"
                                        placeholder="Условия доставки"
                                        cols="30"
                                        rows="6"
                                    />
                                </div>
                            </div>
                            <div v-if="isSupplier" class="row">
                                <div class="col-5">
                                    <h3>Оплата</h3>
                                    <TextareaField
                                        v-model="company.paymentTerm"
                                        :error="errors.paymentTerm"
                                        placeholder="Условия оплаты"
                                        cols="30"
                                        rows="6"
                                    />
                                    <InputField
                                        v-model="company.minOrderAmount"
                                        :error="errors.minOrderAmount"
                                        placeholder="Минимальная сумма заказа"
                                    />
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
import { fetchCompany, saveCompany } from '@/api/company';
import { objectToFormData } from '@/helpers/objectToFormData';
import filepondExternalImage from '@/helpers/filepondExternalImage';
import { sendVerificationRequest } from '@/api/supplier';
import { REQUEST_STATUSES } from '@/constants/requests';
import Error from '@/helpers/error';
import validateMixin from '@/mixins/validateMixin';

export default {
    name: 'CompanyInfo',
    mixins: [
        validateMixin('company', 'errors', {}),
        validateMixin('formData', 'errors', {})
    ],
    async asyncData({ error }) {
        const page = {};

        try {
            page.company = await fetchCompany();
        } catch (e) {
            error({ statusCode: 404 });
        }

        return page;
    },
    storageItems: [
        'myCompanyCommon'
    ],
    data: () => ({
        company: {},
        loading: false,
        formData: {
            image: null,
            gallery: [],
            deleteImagesId: []
        },
        errors: {
            title: '',
            email: '',
            inn: '',
            kpp: '',
            site: '',
            description: '',
            deliveryTerm: '',
            paymentTerm: '',
            minOrderAmount: '',
            image: ''
        }
    }),
    computed: {
        isSupplier() {
            return this.$auth.user.isSupplier;
        },
        galleryModel: {
            get() {
                return (Array.isArray(this.company?.gallery) ? this.company.gallery : []).map(fileObject => (filepondExternalImage(fileObject)));
            },
            set(value) {
                this.formData.gallery = value;
            }
        },
        logoModel: {
            get() {
                return this.company?.image ? [filepondExternalImage(this.company.image)] : [];
            },
            set(value) {
                this.formData.image = value;
            }
        },
        normalizedFormData() {
            const image = this.formData?.image?.[0];
            const formData = {
                ...this.company,
                ...this.formData
            };
            delete formData.canSendVerificationRequest;
            delete formData.lastVerificationRequest;

            if (image instanceof File) {
                formData.image = image;
            } else if (image && image.id) {
                formData.image = image.id;
            } else {
                delete formData.image;
            }
            const gallery = (Array.isArray(formData.gallery) ? formData.gallery : []).filter(image => image instanceof File);
            delete formData.gallery;
            if (gallery.length) {
                formData.gallery = gallery;
            }

            return objectToFormData(formData);
        },
        verificationRequestContent() {
            const last = this.company.lastVerificationRequest;
            const canSendVerificationRequest = this.company.canSendVerificationRequest;
            let text = this.myCompanyCommon?.noVerificationText;
            let status = 'не проверенная';
            let actionTitle = 'Отправить заявку';
            let boxClasses = '';

            if (last) {
                text = '';
            }
            if (last?.answer) {
                text = last.answer;
            }
            if (last?.status?.title) {
                status = last?.status?.title;
            }
            if (last?.status?.code === REQUEST_STATUSES.FAILED) {
                boxClasses = 'box__form-alertwarning'; // Чтобы сделать блок красным
                actionTitle = 'Отправить повторную заявку';
            }

            return {
                text,
                status,
                boxClasses,
                actionTitle,
                canSendVerificationRequest
            };
        }
    },
    methods: {
        async fetchData() {
            this.loading = true;

            await fetchCompany()
                .then(response => {
                    this.company = response;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        async onSend() {
            this.resetErrors();
            this.loading = true;
            await this.doSend();
            this.loading = false;
        },
        async doSend() {
            try {
                const response = await saveCompany({
                    data: this.normalizedFormData
                });

                if (response && typeof response === 'object') {
                    this.company = response;
                }
            } catch (e) {
                const errors = e.response?.data?.error?.request;
                if (errors) {
                    this.errors = errors;
                }
                console.log('Unable to save company info', e);
            }
        },
        onRemoveLogo() {
            this.formData.image = null;
        },
        onRemoveGalleryItem(fileId) {
            this.formData.deleteImagesId.push(fileId);
        },
        async onSendRequest() {
            if (!this.company.canSendVerificationRequest) return;

            try {
                await this.doSend();
                await this.sendVerificationRequest();

                this.$layer.alert({
                    title: 'Заявка отправлена',
                    buttonCaption: 'Закрыть'
                });


                await this.fetchData();
            } catch (e) {
                const errors = e.response?.data?.error?.request;
                if (errors) {
                    this.errors = errors;
                }

                const err = Error.normalize(e);
                if (err?.message) {
                    this.$layer.alert({
                        title: 'Ошибка',
                        message: err?.message
                    });
                }
                console.log('Unable to send verification request', e);
            }
        },
        async sendVerificationRequest() {
            return sendVerificationRequest();
        },
        resetErrors() {
            this.errors = {
                title: '',
                email: '',
                inn: '',
                kpp: '',
                site: '',
                description: '',
                deliveryTerm: '',
                paymentTerm: '',
                minOrderAmount: '',
                imageId: ''
            };
        }
    },
    breadcrumbs() {
        return [
            {
                title: 'Моя организация'
            }
        ];
    }
};
</script>
