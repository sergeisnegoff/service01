import Vue from 'vue';

function validate(rule, value) {
    switch (rule) {
    case 'required':
        if (value === '' || value === null || value === undefined || value === false) {
            throw new Error('Заполните поле');
        }
    }

    return true;
}

export default async function validateForm(formData, formConstraints, fieldErrors) {
    let hasError = false;

    for (let fieldName in formConstraints) {
        if (Object.prototype.hasOwnProperty.call(formConstraints, fieldName)) {
            const constraints = formConstraints[fieldName];
            const value = formData[fieldName];

            Vue.delete(fieldErrors, fieldName);

            try {
                constraints.some(rule => validate(rule, value));

            } catch (error) {
                hasError = true;
                Vue.set(fieldErrors, fieldName, error.message);
            }
        }
    }

    if (hasError) {
        throw new Error('');
    }

    return true;
}
