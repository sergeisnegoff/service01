<template>
    <BaseLayer class="layer_right">
        <template v-slot:close>
            <a href="#" @click.prevent="$emit('close')">
                <svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 28.2843L28.2843 3.09944e-05L30.4056 2.12135L2.12132 30.4056L0 28.2843Z" />
                    <path d="M2.12134 0L30.4056 28.2843L28.2843 30.4056L1.75834e-05 2.12132L2.12134 0Z" />
                </svg>
            </a>
        </template>
        <template v-slot:main>
            <div class="p-5">
                <div class="popup__content">
                    <div class="row">
                        <div class="col-12">
                            <h3>Колонки</h3>
                        </div>
                    </div>
                    <div class="box__form">
                        <form>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Сумма</h4>
                                </div>
                                <div class="col-6">
                                    <div class="box__input">
                                        <input type="text" placeholder="От">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="box__input">
                                        <input type="text" placeholder="До">
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Статус</h4>
                                </div>
                                <div class="col-12">
                                    <div class="box__select">
                                        <select>
                                            <option value="1-1" selected="selected">
                                                Статус
                                            </option>
                                            <option value="1-2">
                                                Option 1.2
                                            </option>
                                            <option value="1-3">
                                                Option 1.3
                                            </option>
                                            <option value="2-1">
                                                Option 2.1
                                            </option>
                                            <option value="2-2">
                                                Option 2.2
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Организация</h4>
                                </div>
                                <div class="col-12">
                                    <div class="box__select">
                                        <select>
                                            <option value="1-1" selected="selected">
                                                Организация
                                            </option>
                                            <option value="1-2">
                                                Option 1.2
                                            </option>
                                            <option value="1-3">
                                                Option 1.3
                                            </option>
                                            <option value="2-1">
                                                Option 2.1
                                            </option>
                                            <option value="2-2">
                                                Option 2.2
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Торговая точка</h4>
                                </div>
                                <div class="col-12">
                                    <div class="box__select">
                                        <select>
                                            <option value="1-1" selected="selected">
                                                Торговая точка
                                            </option>
                                            <option value="1-2">
                                                Option 1.2
                                            </option>
                                            <option value="1-3">
                                                Option 1.3
                                            </option>
                                            <option value="2-1">
                                                Option 2.1
                                            </option>
                                            <option value="2-2">
                                                Option 2.2
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                            <option value="2-3">
                                                Option 2.3
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Дата поставки</h4>
                                </div>
                                <div class="col-6">
                                    <div class="box__input">
                                        <span class="input__icon" style="background-image: url(${require('../../img/icon/calendar.svg')});"></span>
                                        <input type="text" placeholder="От">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="box__input">
                                        <span class="input__icon" style="background-image: url(${require('../../img/icon/calendar.svg')});"></span>
                                        <input type="text" placeholder="До">
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-12">
                                    <h4>Дата создания</h4>
                                </div>
                                <div class="col-6">
                                    <div class="box__input">
                                        <span class="input__icon" style="background-image: url(${require('../../img/icon/calendar.svg')});"></span>
                                        <input type="text" placeholder="От">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="box__input">
                                        <span class="input__icon" style="background-image: url(${require('../../img/icon/calendar.svg')});"></span>
                                        <input type="text" placeholder="До">
                                    </div>
                                </div>
                            </div>
                            <div class="row margin-15">
                                <div class="col-6">
                                    <div class="btn">
                                        <button @click.prevent="$emit()">
                                            Применить
                                        </button>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="btn-whitepurple text-right">
                                        <button @click.prevent="reset">
                                            Сбросить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </BaseLayer>
</template>

<script>
export default {
    name: 'UserAddRoleLayer',
    model: {
        prop: 'formData',
        event: 'submit'
    },
    props: {
        formData: { type: Object, default: () => ({}) }
    },
    data() {
        return {
            formDataLocal: JSON.parse(JSON.stringify(this.formData))
        };
    },
    methods: {
        reset() {

        }
    }
};
</script>
