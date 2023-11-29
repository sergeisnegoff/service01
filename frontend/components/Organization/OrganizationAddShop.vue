<template>
    <div
        v-bind="$attrs"
    >
        <slot v-bind="{ data, doClear }"></slot>
    </div>
</template>
<script>
export default {
    name: 'OrganizationAddShop',
    data() {
        return {
            data: {}
        };
    },
    methods: {
        doSave() {
            this.$emit('save', this.data);
        },
        doClear() {
            this.data = {};
        }
    }
};
</script>
