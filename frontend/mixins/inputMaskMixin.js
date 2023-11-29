export default {
    props: {
        mask: {}
    },

    async mounted() {
        if (!this.mask) {
            return;
        }
        const _this = this;

        let options = {
            jitMasking: 0,
            clearMaskOnLostFocus: false,
            showMaskOnHover: false,
            showMaskOnFocus: true,
            onBeforePaste(pastedValue) {
                const formattedValue = this.format(pastedValue);
                _this.$emit('input', formattedValue);

                return formattedValue;
            },
            onBeforeWrite(event, value) {
                _this.$emit('input', value.join(''));
            }
        };

        if (this.mask === 'phone') {
            options = {
                ...options,
                mask: '+7 (999) 999-99-99',
                placeholder: '+7 (___) ___-__-__'
            };
        } else if (this.mask === 'number') {
            options = {
                ...options,
                mask: '9{1,}[.9{1,}]',
                placeholder: ''
            };
        } else if (this.mask === 'email') {
            options = {
                ...options,
                mask: /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/
            };
        } else if (typeof this.mask === 'string') {
            options = {
                ...options,
                mask: this.mask
            };

        } else {
            options = {
                ...options,
                ...this.mask
            };
        }

        const { default: Inputmask } = await import('inputmask');
        this._im = new Inputmask(options);
        this._im.mask(this.$refs.input);
    },

    beforeDestroy() {
        if (this._im) {
            this._im.remove();
        }
    }
};
