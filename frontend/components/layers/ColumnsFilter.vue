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
            <div class="p-5">
                <div class="popup__content">
                    <div class="row mb-5">
                        <div class="col-12">
                            <h3>Колонки</h3>
                        </div>
                    </div>
                    <div v-for="key in columnKeys" :key="key" class="row">
                        <div class="col-12">
                            <div class="box__checkbox">
                                <div class="wrapper-checkbox">
                                    <label>
                                        <input v-model="formData[key]" type="checkbox">
                                        <span>
                                            <span class="box__checkbox-icon"></span>
                                            <span class="box__checkbox-text">
                                                {{ columns[key].title }}
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="btn">
                                <button @click.prevent="onClose">
                                    Показать
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </BaseLayer>
</template>

<script>
export default {
    name: 'ColumnsFilter',
    props: {
        columns: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            formData: {}
        };
    },
    computed: {
        columnKeys() {
            return Object.keys(this.columns);
        }
    },
    beforeMount() {
        (Array.isArray(this.columnKeys) ? this.columnKeys : []).forEach(key => {
            this.formData[key] = this.columns[key].isVisible;
        });
    },
    methods: {
        onClose() {
            this.$emit('close', { columns: this.formData });
        }
    }
};
</script>
