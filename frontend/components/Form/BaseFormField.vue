<template>
    <div
        :class="{
            'filled': !!value,
            'error': !!error,
            'is-required': required,
            'is-disabled': disabled,
            ['field_theme_' + theme]: theme,
            ['field_view_' + view]: view,
            'focused': isFocused
        }"
    >
        <slot v-bind="inputProps"></slot>
        <slot name="title">
            <label v-if="title" :for="id" class="field__title" v-html="title"></label>
        </slot>
        <label v-if="error && error.toString().trim().length" class="invalid-feedback" :for="id" v-html="error"></label>
        <template v-if="'side' in $slots">
            <div class="field__side">
                <slot name="side"></slot>
            </div>
        </template>
    </div>
</template>

<script>
import formFieldMixin from '@/mixins/formFieldMixin';

export default {
    mixins: [formFieldMixin],
    props: {
        theme: { type: String, default: 'outline-blue-500' },
        view: { type: String },
        isFocused: { type: Boolean }
    }
};
</script>
