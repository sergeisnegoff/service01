<template>
    <section class="box__buyers__page">
        <div class="container">
            <div class="row align-center">
                <div class="col-6">
                    <h1>Меркурий</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <MercuryNavigation />
                </div>
            </div>
            <div
                class="row"
                :class="{
                    '-preloader': pending
                }"
            >
                <div class="col-12 margin-30">
                    <h4>Гашение ВСД</h4>
                    <div class="box__checkbox">
                        <div class="wrapper-checkbox">
                            <label>
                                <input v-model="isAutoRepayment" type="checkbox" @change="onRepaymentChange">
                                <span>
                                    <span class="box__checkbox-icon"></span>
                                    <span class="box__checkbox-text">Включить автогашение</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { fetchMercurySettings, setVSDRepayment } from '@/api/mercury';

export default {
    name: 'MercurySettingsPage',
    fetch() {
        return this.fetchSettings();
    },
    data() {
        return {
            isAutoRepayment: false,
            pending: false
        };
    },
    computed: {},
    methods: {
        async fetchSettings() {
            this.pending = true;
            try {
                return fetchMercurySettings()
                    .then(response => {
                        this.isAutoRepayment = response.isAutoRepayment;
                        this.pending = false;
                    });
            } catch (e) {
                this.pending = false;
                console.log('Unable to fetch mercury settings', e);
            }
        },
        changeAutoRepayment() {
            this.pending = true;
            try {
                return setVSDRepayment().then((response) => {
                    this.isAutoRepayment = response.isAutoRepayment;
                    this.pending = false;
                });
            } catch (e) {
                this.pending = false;
                console.log('Unable to set auto repayment', e);
            }
        },
        async onRepaymentChange() {
            await this.changeAutoRepayment();
            await this.fetchSettings();
        }
    },
    breadcrumbs() {
        const items = [];

        items.push(
            {
                title: 'Меркурий',
                link: {
                    name: 'mercury'
                }
            },
            {
                title: 'Настройки'
            }
        );

        return items;
    }
};
</script>
