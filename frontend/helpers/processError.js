import Vue from 'vue';
import scrollIntoView from '@/helpers/scrollIntoView';

export default async function processError(error, fieldErrors) {
    if (error.isAxiosError) {
        if (!error.response) return;

        error = error.response.data.error;

        if (fieldErrors) {
            for (let i in fieldErrors) {
                if (Object.prototype.hasOwnProperty.call(fieldErrors, i)) {
                    Vue.delete(fieldErrors, i);
                }
            }

            if (error.request) {
                for (let i in error.request) {
                    if (Object.prototype.hasOwnProperty.call(error.request, i)) {
                        Vue.set(fieldErrors, i, error.request[i]);
                    }
                }
            }
            if (!window) return;

            await Vue.prototype.$nextTick();
            const firstErrorField = document.querySelector('.field.error');
            if (firstErrorField) {
                firstErrorField.querySelector('input');

                scrollIntoView(firstErrorField, { y: -40 });
            }
        }
    }

    if (error.message) {
        Vue.prototype.$layer.alert({
            message: 'Ошибка',
            description: error.message,
            type: 'error'
        });
    }
}
