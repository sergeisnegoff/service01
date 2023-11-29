<template>
    <div
        v-bind="$attrs"
    >
        <div class="box__item box__item-edit active" data-table>
            <form @submit.prevent="doSave">
                <div class="row">
                    <div class="col-2">
                        <InputField
                            v-model="data.title"
                            placeholder="Название"
                            :error="errors.title"
                        />
                    </div>
                    <div class="col-3">
                        <InputField
                            v-model="data.inn"
                            placeholder="ИНН"
                            :error="errors.inn"
                        />
                    </div>
                    <div class="col-2">
                        <InputField
                            v-model="data.kpp"
                            placeholder="КПП"
                            :error="errors.kpp"
                        />
                    </div>
                    <div class="col-5">
                        <div class="wraapper__button-nowrap content__justify-end">
                            <div class="btn btn__icon-purple">
                                <BaseButton type="submit">
                                    <span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span>
                                </BaseButton>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
<script>
export default {
    name: 'OrganizationAdds',
    data() {
        return {
            data: {
                title: '',
                inn: '',
                kpp: ''
            },
            errors: {
                title: '',
                inn: '',
                kpp: ''
            }
        };
    },
    methods: {
        doSave() {
            if (!this.isFormValid()) return;

            this.$emit('save', { data: this.data, clearForm: this.doClear });
        },
        doClear() {
            this.data = {
                title: '',
                inn: '',
                kpp: ''
            };
            this.errors = {
                title: '',
                inn: '',
                kpp: ''
            };
        },
        isFormValid() {
            const dataFields = ['title', 'inn', 'kpp'];
            const emptyError = 'Заполните поле';
            dataFields.forEach(field => {
                if (!this.data[field]) {
                    this.errors[field] = emptyError;
                } else {
                    this.errors[field] = '';
                }
            });

            return dataFields.every(field => this.data[field]);
        }
    }
};
</script>
