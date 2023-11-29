<template>
    <component :is="tag" v-bind="$attrs">
        <slot v-bind="{ isEdit, doEdit, doCancel, doSave, doClear, data }"></slot>
    </component>
</template>
<script>
export default {
    name: 'EditableRowWrapper',
    props: {
        item: {
            type: Object
        },
        tag: {
            type: String,
            default: 'div'
        }
    },
    data() {
        return {
            data: {}
        };
    },
    computed: {
        isEdit() {
            return this.item?.isEdit;
        },
        allData() {
            return {
                ...(this.item || {}),
                ...this.data
            };
        }
    },
    methods: {
        doEdit() {
            this.$emit('edit', {
                item: this.allData
            });
        },
        doCancel() {
            this.$emit('cancel', {
                item: this.allData
            });
        },
        doSave() {
            this.$emit('save', {
                item: this.allData
            });
        },
        doClear() {
            this.data = {};
        }
    }
};
</script>
