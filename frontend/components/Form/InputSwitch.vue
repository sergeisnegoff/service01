<template>
    <BaseFormField
        v-bind="$props"
        :is-focused="isInputFocused"
        class="box__switch"
    >
        <slot>
            <div class="wrapper-switch">
                <label>
                    <input
                        v-bind="inputPropsCombined"
                        :type="type"
                        :checked="inputValue"
                        @focus="isInputFocused = true"
                        @blur="isInputFocused = false"
                        v-on="inputListeners"
                        @change="$emit('change', $event.target.checked)"
                    >
                    <span>
                        <span class="box__radiobox-icon"></span>
                    </span>
                </label>
            </div>
        </slot>
        <template v-slot:side>
            <slot name="side"></slot>
        </template>
    </BaseFormField>
</template>
<script>
import BaseFormField from './BaseFormField';

export default {
    name: 'RadioSwitch',
    extends: BaseFormField,
    model: {
        prop: 'value',
        event: 'change'
    },
    props: {
        type: {
            type: String,
            default: 'checkbox'
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
                type: this.type
            };
        }
    }
};
</script>
<style>
    .box__switch input + span .box__radiobox-icon {
        transition: .25s ease-in-out;
    }
    .box__switch.filled input + span .box__radiobox-icon {
        left: auto;
        right: 2px;
    }
    .box__switch.filled .wrapper-switch input + span{
        background-color: var(--color-8);
    }
</style>
