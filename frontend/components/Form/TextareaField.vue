<template>
    <BaseFormField
        v-bind="$props"
        class="box__textarea"
    >
        <textarea ref="input" v-model="inputValue" v-bind="inputPropsCombined" v-on="inputListeners"></textarea>
    </BaseFormField>
</template>


<script>
import BaseFormField from './BaseFormField';

export default {
    extends: BaseFormField,
    props: {
        autocomplete: String,
        maxlength: Number,
        theme: { type: String, default: '' }
    },
    computed: {
        inputPropsCombined() {
            return {
                ...this.inputProps,
                autocomplete: this.autocomplete,
                maxlength: this.maxlength
            };
        }
    }
};
</script>
