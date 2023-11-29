<template>
    <div class="layer">
        <div class="layer__wrapper text-center py-10">
            <h3 v-if="title" class="mb-2" v-html="title"></h3>
            <div v-if="description" v-html="description"></div>
            <div v-if="message" v-html="message"></div>
            <div class="flex items-center justify-center mt-6">
                <BaseButton
                    ref="button"
                    type="button"
                    class="mr-3"
                    @click="$emit('close', true)"
                >
                    {{ acceptButtonCaption }}
                </BaseButton>
                <BaseButton
                    type="button"
                    @click="$emit('close')"
                >
                    {{ declineButtonCaption }}
                </BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: 'Подтверждение'
        },
        description: {
            type: String
        },
        message: {
            type: String,
            default: 'Вы уверены, что хотите это сделать?'
        },
        acceptButtonCaption: {
            type: String,
            default: 'Да'
        },
        declineButtonCaption: {
            type: String,
            default: 'Нет'
        }
    },
    mounted() {
        this.$refs.button?.$el?.focus();
    }
};
</script>
