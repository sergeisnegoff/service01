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
            <div class="px-16 py-14">
                <form class="px-1" @submit.prevent="submit">
                    <h2 class="text-center">
                        Запрос на работу
                    </h2>
                    <div class="mb-8">
                        <TextareaField
                            v-model="formData.message"
                            :error="errors.message"
                        />
                    </div>
                    <div class="text-center">
                        <BaseButton type="submit">
                            Отправить
                        </BaseButton>
                    </div>
                </form>
            </div>
        </template>
    </BaseLayer>
</template>

<script>
export default {
    name: 'RequestJobLayer',
    data() {
        return {
            formData: {
                message: ''
            },
            errors: {
                message: ''
            }
        };
    },
    methods: {
        submit() {
            this.$emit('close', this.formData.message || true);
        }
    }
};
</script>
