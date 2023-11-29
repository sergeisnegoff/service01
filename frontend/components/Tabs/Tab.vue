<template>
    <div v-show="isActive">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: 'Tab',
    props: {
        settings: {
            type: Object,
            required: true
        },
        selected: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        isActive() {
            return this.selected;
        }
    }
};
</script>
