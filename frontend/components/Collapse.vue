<template>
    <div
        :is="className"
        v-bind="$attrs"
        v-on="allListeners"
    >
        <slot v-bind="{ isOpened, doToggle, doOpen, doClose }">
        </slot>
    </div>
</template>
<script>
export default {
    name: 'Collapse',
    props: {
        className: {
            type: String,
            default: 'div'
        },
        allOpened: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            isOpened: this.allOpened
        };
    },
    computed: {
        allListeners() {
            return {
                ...(this.$listeners)
            };
        }
    },
    methods: {
        doToggle() {
            this.isOpened = !this.isOpened;
        },
        doOpen() {
            this.isOpened = true;
        },
        doClose() {
            this.isOpened = false;
        }
    }
};
</script>
