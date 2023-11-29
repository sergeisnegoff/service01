import { generateUuid } from '@/plugins/uuid';

export default {
    props: {
        inputListeners: { type: Object, default: () => ({}) },
        title: { type: String },
        error: { type: String },
        placeholder: { type: String },
        value: {},
        id: { type: String, default: () => generateUuid('form-field-') },
        type: { type: String },
        disabled: { type: Boolean },
        required: { type: Boolean }
    },
    computed: {
        inputValue: {
            get() {
                return this.value;
            },
            set(value) {
                this.$emit('input', value);
            }
        },
        inputProps() {
            return {
                id: this.id,
                type: this.type,
                // value: this.value,
                placeholder: this.placeholder,
                disabled: this.disabled,
                required: this.required
            };
        }
    },
    methods: {
        focus() {
            this.$refs.input.click();
        }
    }
};
