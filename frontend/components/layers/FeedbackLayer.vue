<template>
    <BaseLayer>
        <template v-slot:close>
            <a href="#" @click.prevent="$emit('close')">
                <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 28.2843L28.2843 3.09944e-05L30.4056 2.12135L2.12132 30.4056L0 28.2843Z" />
                    <path d="M2.12134 0L30.4056 28.2843L28.2843 30.4056L1.75834e-05 2.12132L2.12134 0Z" />
                </svg>
            </a>
        </template>
        <template v-slot:main>
            <form
                :class="{
                    '-preloader': subject.loading
                }"
                @submit.prevent="submit"
            >
                <div class="p-5">
                    <div class="px-7">
                        <h2 class="text-center margin-15">
                            Сообщить о проблеме
                        </h2>
                        <div class="mb-3">
                            <SelectField
                                v-model="formData.subject"
                                :options="subject.items"
                                track-by="id"
                                label="title"
                                placeholder="Тема"
                                :error="errors.subject_id"
                            />
                        </div>
                        <div class="mb-4">
                            <TextareaField
                                v-model="formData.text"
                                :maxlength="2000"
                                placeholder="Сообщение"
                                :error="errors.text"
                            />
                        </div>
                        <div class="mb-4">
                            <FileController
                                :files="formData.file"
                                :types="['png', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx']"
                                :max-size="5"
                                @check-end="formData.file = $event"
                            >
                                <FileField
                                    v-model="formData.file"
                                    :error="errors.file"
                                    label="Загрузить файл"
                                    :multiple="true"
                                />
                            </FileController>
                        </div>
                        <div class="text-center">
                            <BaseButton
                                :disabled="sending"
                                type="submit"
                                class="margin-15"
                            >
                                Отправить запрос
                            </BaseButton>
                        </div>
                    </div>
                </div>
            </form>
        </template>
    </BaseLayer>
</template>
<script>
import { sendForm, fetchReportSubjects } from '@/api/form';
import { objectToFormData } from '@/helpers/objectToFormData';

export default {
    name: 'FeedbackLayer',
    fetch() {
        return Promise.all([
            this.fetchSubjects()
        ]);
    },
    data() {
        return {
            subject: {
                items: [],
                loading: false
            },
            sending: false,
            formData: {
                file: null,
                subject: null,
                text: ''
            },
            errors: {}
        };
    },
    computed: {
        normalizedFormData() {
            return {
                subject_id: this.formData.subject?.id,
                text: this.formData.text,
                file: this.formData.file?.[0]
            };
        }
    },
    methods: {
        fetchSubjects() {
            this.subject.loading = true;

            return fetchReportSubjects({ progress: false })
                .then((response) => {
                    this.subject.items = response;
                })
                .finally(() => {
                    this.subject.loading = false;
                });
        },
        async submit() {

            this.sending = true;
            try {
                const result = await sendForm('reportProblem', objectToFormData(this.normalizedFormData));

                this.closeLayer();
                this.$layer.alert({
                    title: 'Спасибо',
                    message: result && result.success_text || 'Ваше сообщение отправлено'
                });
            } catch (e) {
                const error = e.response?.data?.error;
                const errorMessage = error?.message;
                const requestErrors = error?.request;

                if (requestErrors) {
                    this.errors = requestErrors;
                }

                if (errorMessage) {
                    this.$layer.alert({
                        title: 'Ошибка',
                        message: errorMessage
                    });
                }
                console.log('Unable to send form', e);
            }

            this.sending = false;
        },
        closeLayer() {
            this.$emit('close');
        }
    }
};
</script>
