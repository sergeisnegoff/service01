<template>
    <div
        class="select input"
        :class="{
            'error': !!error
        }"
    >
        <label v-if="title" :for="id" class="select-title" v-html="title"></label>
        <BaseSelect
            ref="select"
            v-model="inputValue"
            class="multiselect_field"
            :options="options"
            v-bind="inputPropsCombined"
            v-on="inputListeners"
            @open="setCoords"
            @close="onClose"
        >
            <template slot="option" slot-scope="props">
                <slot name="option" v-bind="props">
                    <div
                        :class="{
                            multiselect__option_sub: props.option.isSub
                        }"
                    >
                        <div class="box__checkbox">
                            <div class="wrapper-checkbox">
                                <label class="pointer-events-none">
                                    <input :checked="value && +value.id === +props.option.id" type="checkbox">
                                    <span>
                                        <span class="box__checkbox-icon"></span>
                                        <span class="box__checkbox-text">
                                            {{ props.option[inputPropsCombined.label || 'title'] }}
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </slot>
            </template>
            <template v-slot:noResult>
                <span v-html="noResult"></span>
            </template>
            <template #placeholder>
                <BaseTooltip arrow="true">
                    <template #trigger>
                        <div class="overflow-hidden overflow-ellipsis">
                            {{ placeholder || 'Выберите вариант' }}
                        </div>
                    </template>
                    {{ placeholder || 'Выберите вариант' }}
                </BaseTooltip>
            </template>
            <template v-slot:noOptions>
                <span v-html="noOptions"></span>
            </template>
        </BaseSelect>
        <label v-if="error" :for="id" class="invalid-feedback" v-html="error"></label>
    </div>
</template>

<script>
import formFieldMixin from '@/mixins/formFieldMixin';
import tooltipSelectMixin from '@/mixins/tooltipSelectMixin';

export default {
    name: 'SelectField',
    mixins: [formFieldMixin, tooltipSelectMixin],
    props: {
        type: { type: String, default: 'text' },
        autocomplete: String,
        inputmode: String,
        maxlength: Number,
        error: String,
        options: { type: Array, default: () => [] },
        noOptions: { type: String, default: 'Нет данных' },
        noResult: { type: String, default: 'Ничего не найдено' }
    },
    computed: {
        inputPropsCombined() {
            return {
                ...this.inputProps,
                ...this.$attrs
            };
        }
    }
};
</script>
