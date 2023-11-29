<template>
    <div class="layer layer_alert">
        <div class="layer__wrapper text-center py-10">
            <h3 v-if="title" v-html="title"></h3>
            <div v-if="description" v-html="description"></div>
            <div v-if="message" v-html="message"></div>
            <div class="mt-6">
                <BaseButton
                    ref="button"
                    type="button"
                    @click.prevent="$emit('close')"
                >
                    {{ buttonCaption }}
                </BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String
        },
        description: {
            type: String
        },
        message: {
            type: String
        },
        buttonCaption: {
            type: String,
            default: 'Закрыть'
        }
    },
    async mounted() {
        await this.$nextTick();
        this.$refs.button.focusOnButton();
    }
};
</script>
