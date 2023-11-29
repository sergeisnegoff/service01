<template>
    <BaseLayer class="layer_right">
        <template v-slot:close>
            <a href="#" @click.prevent="$emit('close')">
                <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 28.2843L28.2843 3.09944e-05L30.4056 2.12135L2.12132 30.4056L0 28.2843Z" />
                    <path d="M2.12134 0L30.4056 28.2843L28.2843 30.4056L1.75834e-05 2.12132L2.12134 0Z" />
                </svg>
            </a>
        </template>
        <template v-slot:main>
            <div class="p-5">
                <div class="popup__content">
                    <div class="row">
                        <div class="col-12">
                            <h3>Фильтрация</h3>
                        </div>
                    </div>
                    <div class="box__form">
                        <form @submit.prevent="">
                            <div v-if="attributes.sender && attributes.sender.length" class="row margin-15">
                                <div class="col-12">
                                    <h4>Отправитель</h4>
                                </div>
                                <div class="col-12">
                                    <SelectField
                                        v-model="formData.sender"
                                        :options="attributes.sender"
                                        track-by="id"
                                        placeholder="Отправитель"
                                        label="title"
                                        :error="errors.sender"
                                    />
                                </div>
                            </div>
                            <div v-if="attributes.status && attributes.status.length" class="row margin-15">
                                <div class="col-12">
                                    <h4>Статус документа</h4>
                                </div>
                                <div class="col-12">
                                    <SelectField
                                        v-model="formData.status"
                                        :options="attributes.status"
                                        track-by="id"
                                        placeholder="Статус документа"
                                        label="title"
                                        :error="errors.status"
                                    />
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Дата оформления ВСД</h4>
                                </div>
                                <div class="col-12">
                                    <div class="box__input">
                                        <DatePicker
                                            v-model="formData.issueDate"
                                            placeholder="Выберите дату"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-6">
                                    <div class="btn">
                                        <button @click.prevent="onApply">
                                            Применить
                                        </button>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="btn-whitepurple text-right">
                                        <button @click.prevent="reset">
                                            Сбросить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </BaseLayer>
</template>

<script>
export default {
    name: 'MercuryFilter',
    model: {
        prop: 'formData',
        event: 'close'
    },
    props: {
        formData: {
            type: Object
        },
        attributes: {
            type: Object
        },
        errors: {
            type: Object
        }
    },
    data() {
        return {};
    },
    computed: {},
    methods: {
        onApply() {
            this.$emit('close', this.formData);
        },
        reset() {
            this.formData.sender = [];
            this.formData.status = [];
            this.formData.issueDate = null;
        }
    }
};
</script>
