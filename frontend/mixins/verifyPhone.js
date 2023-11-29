import { requestVerification, verifyCode } from '@/api/verification';
import { AUTH_STEPS } from '@/constants/auth';
import processError from '@/helpers/processError';

export default {
    data() {
        return {
            phoneConfirmLoading: false,
            formData: {
                phone: '',
                code: ''
            },
            errors: {
                phone: '',
                code: ''
            }
        };
    },
    computed: {
        isPhoneSubmitGranted() {
            return this.formData.phone.length === '+7 (999) 999-99-99'.length && this.formData.fullName;
        }
    },
    watch: {
        'formData.phone'() {
            this.errors.phone = '';
        },
        'formData.code'() {
            this.errors.code = '';
        }
    },
    methods: {
        async onVerifyCode() {
            this.phoneConfirmLoading = true;

            try {
                await requestVerification('phone', {
                    key: this.formData.phone
                }).then(response => {
                    if (response && response.code) {
                        this.formData.code = response.code;
                    }
                });
                this.step = AUTH_STEPS.CONFIRM;
            } catch (e) {
                console.log(e);
                processError(e, this.errors);
            }

            this.phoneConfirmLoading = false;
        },
        async onConfirmCode() {
            this.phoneConfirmLoading = true;

            try {
                const { verified } = await verifyCode('phone', {
                    key: this.formData.phone,
                    code: this.formData.code
                });
                if (!verified) {
                    this.errors.code = 'Неверный код';
                    this.phoneConfirmLoading = false;

                    return;
                }
                this.step = AUTH_STEPS.PASSWORD;
            } catch (e) {
                processError(e, this.errors);
            }

            this.phoneConfirmLoading = false;
        }
    }
};
