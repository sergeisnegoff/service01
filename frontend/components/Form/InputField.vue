<template>
    <BaseFormField
        v-bind="$props"
        :theme="theme"
        :view="view"
        :is-focused="isInputFocused"
        class="box__input"
    >
        <slot>
            <slot v-if="'icon' in $slots" name="icon"></slot>
            <input
                ref="input"
                v-model="inputValue"
                class="field__input"
                :class="{
                    'is-invalid': error
                }"
                v-bind="inputPropsCombined"
                @focus="onFocus"
                @blur="isInputFocused = false"
                @keyup="onKeyup"
                v-on="inputListeners"
            >
            <span v-if="caption" v-html="caption"></span>
        </slot>
        <template v-slot:side>
            <slot name="side"></slot>
        </template>
    </BaseFormField>
</template>

<script>
import BaseFormField from './BaseFormField';
import inputMaskMixin from '@/mixins/inputMaskMixin';

export default {
    name: 'InputField',
    extends: BaseFormField,
    mixins: [inputMaskMixin],
    props: {
        type: { type: String, default: 'text' },
        autocomplete: String,
        inputmode: String,
        maxlength: Number,
        theme: { type: String, default: '' },
        view: { type: String },
        mask: {
            type: String,
            default: ''
        },
        caption: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            isInputFocused: false
        };
    },
    computed: {
        inputPropsCombined() {
            return {
                ...this.inputProps,
                autocomplete: this.autocomplete,
                maxlength: this.maxlength,
                inputmode: this.inputmode
            };
        }
    },
    methods: {
        onFocus() {
            this.isInputFocused = true;
        },
        onKeyup(val) {
            this.$emit('keyup', val.target.value);
        }
    }
};
</script>
