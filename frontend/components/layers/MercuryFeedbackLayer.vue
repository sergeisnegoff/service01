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
            <form @submit.prevent="submit">
                <div class="p-5">
                    <div class="px-7">
                        <h2 class="text-center margin-15">
                            Сообщить о проблеме со справкой
                        </h2>
                        <div class="mb-4">
                            <TextareaField
                                v-model="formData.reason"
                                :maxlength="2000"
                                placeholder="Причина"
                                :error="formErrors.reason"
                            />
                        </div>
                        <div class="text-center">
                            <BaseButton
                                :disabled="sending"
                                type="submit"
                                class="margin-15"
                            >
                                Отправить
                            </BaseButton>
                        </div>
                    </div>
                </div>
            </form>
        </template>
    </BaseLayer>
</template>
<script>
import { sendProblems } from '@/api/mercury';
import processError from '@/helpers/processError';
import { objectToFormData } from '@/helpers/objectToFormData';

export default {
    name: 'FeedbackLayer',
    props: {
        documentIds: { type: Array, default: () => [] }
    },
    data() {
        return {
            sending: false,
            formData: {
                reason: '',
                documentIds: this.documentIds
            },
            formErrors: {}
        };
    },
    methods: {
        async submit() {
            this.sending = true;

            try {
                await sendProblems(objectToFormData(this.formData));

                this.$emit('close');
                this.$layer.alert({
                    title: 'Спасибо',
                    message: 'Ваше сообщение отправлено'
                });
            } catch (e) {
                console.log(e);
                processError(e, this.formErrors);
            }

            this.sending = false;
        }
    }
};
</script>
