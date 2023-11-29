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
                <div class="px-7">
                    <div class="mb-4">
                        <div class="text-center">
                            {{ text }}
                        </div>
                    </div>
                    <div class="text-center">
                        <BaseButton
                            type="submit"
                            class="margin-15"
                            @click.native="openFeedback"
                        >
                            Написать в поддержку
                        </BaseButton>
                    </div>
                </div>
            </div>
        </template>
    </BaseLayer>
</template>
<script>
export default {
    name: 'FeedbackLayer',
    props: {
        text: { type: String }
    },
    methods: {
        openFeedback() {
            this.$layer.open('FeedbackLayer');
            this.$emit('close');
        }
    }
};
</script>
