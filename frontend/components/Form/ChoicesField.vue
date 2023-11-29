<template>
    <div class="box__select">
        <select
            ref="select"
        >
            <option
                v-for="option in options"
                :key="option.id"
                :value="option.id"
            >
                {{ option.title }}
            </option>
        </select>
        <span v-if="error" class="text-red-500" v-html="error"></span>
    </div>
</template>


<script>
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

import PerfectScrollbar from 'perfect-scrollbar';

export default {
    props: {
        options: {
            type: Array,
            default: () => ([])
        },
        value: {
            type: String, Number,
            default: null
        },
        placeholder: {
            type: String,
            default: ''
        },
        error: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            choicesInstance: null
        };
    },
    computed: {
        optionsNormalized() {
            if (this.placeholder) {
                return [
                    {
                        id: '',
                        selected: true,
                        title: this.placeholder
                    },
                    ...this.options
                ];
            }

            return this.options;
        }
    },
    watch: {
        options: function() {
            this.setChoices();
        }
    },
    mounted() {
        if (!this.$refs.select) return;
        console.log('config', {
            searchEnabled: false,
            searchChoices: false,
            removeItemButton: false,
            itemSelectText: 'Нажмите, чтобы выбрать',
            placeholder: Boolean(this.placeholder),
            placeholderValue: this.placeholder
        });

        this.choicesInstance = new Choices(this.$refs.select, {
            searchEnabled: false,
            searchChoices: false,
            removeItemButton: false,
            itemSelectText: 'Нажмите, чтобы выбрать',
            placeholder: Boolean(this.placeholder),
            placeholderValue: this.placeholder,
            searchPlaceholderValue: this.placeholder,
            callbackOnInit() {
                if (!this.dropdown.element) return;
                new PerfectScrollbar(this.dropdown.element, {
                    wheelSpeed: 0.4,
                    suppressScrollX: true,
                    wheelPropagation: true,
                    minScrollbarLength: 20
                });
            }
        });
        this.$refs.select.addEventListener('addItem', this.handleSelectChange);
        this.setChoices();
    },
    destroyed: function() {
        this.choicesInstance && this.choicesInstance.destroy();
    },
    methods: {
        handleSelectChange(e) {
            this.$emit('input', e.target.value);
        },
        setChoices() {
            this.choicesInstance && this.choicesInstance.setChoices(this.optionsNormalized, 'id', 'title', true);
        }
    }
};
</script>
