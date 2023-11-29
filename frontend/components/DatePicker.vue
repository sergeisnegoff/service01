<template>
    <div>
        <flat-pickr
            v-model="inputValue"
            :placeholder="placeholder"
            :config="config"
        />
    </div>
</template>

<script>
import { Russian } from 'flatpickr/dist/l10n/ru.js';

export default {
    props: {
        value: {
            type: [String, Object]
        },
        placeholder: {
            type: String
        }
    },
    data: () => ({
        config: {
            dateFormat: 'd.m.Y',
            locale: Russian
        }
    }),
    computed: {
        inputValue: {
            get() {
                return this.value;
            },
            set(value) {
                this.$emit('input', value);
            }
        }
    }
};
</script>
