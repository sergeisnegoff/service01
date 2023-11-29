<template>
    <div>
        <slot></slot>
    </div>
</template>
<script>
export default {
    props: {
        maxSize: { type: Number },
        maxItems: { type: Number, default: Infinity },
        canLoadAmount: { type: Number, default: Infinity },
        types: { type: Array, default: () => [] },
        files: { type: Array, default: () => [] }
    },
    watch: {
        files(val, prevVal) {
            if (val && prevVal && val.length !== prevVal.length) {
                this.checkErrors();
            }
        }
    },
    methods: {
        getNormalizedType(file) {
            return file.name.split('.').slice(-1)
                .join('');
        },
        getConvertedSizeToMb(size) {
            if (size) {
                return (size / (1024 * 1024)).toFixed(2) + ' МБ';
            } else {
                return '0 МБ';
            }
        },
        checkErrors() {
            const files = this.files;
            const resultFiles = [];
            let result = true;

            if (files.length > this.canLoadAmount) {
                this.$layer.alert({
                    message: 'Ошибка',
                    description: `Максимальное количество файлов - ${ this.maxItems }`
                });

                result = false;
                this.$emit('check-end', this.files.slice(0, this.maxItems));
            }

            files.forEach(item => {
                let wasError = false;

                if (this.types.length && !this.types.includes(this.getNormalizedType(item))) {
                    if (!this.$layer || !result) return;

                    this.$layer.alert({
                        title: 'Ошибка',
                        description: 'Выбранный формат не поддерживается'
                    });

                    result = false;
                    wasError = true;
                }

                if (this.maxSize && parseFloat(this.getConvertedSizeToMb(item.size)) > this.maxSize) {
                    if (!this.$layer || !result) return;

                    this.$layer.alert({
                        title: 'Ошибка',
                        description: `Максимальный допустимый размер файла - ${ this.maxSize } Мб`
                    });

                    result = false;
                    wasError = true;
                }

                if (!wasError) resultFiles.push(item);
            });

            this.$emit('check-end', resultFiles);
        }
    }
};
</script>
