<template>
    <section class="box__suppliers__page">
        <CompanyInfo :mode="companyMode" />
    </section>
</template>
<script>
export default {
    name: 'BuyerPage',
    data() {
        return {};
    },
    computed: {
        /**
         * Если поставщик, покажем информацию о организации в режиме покупателя
         * а если покупатель, покажем информацию о организации в режиме поставщика
         *
         * @returns {string}
         */
        companyMode() {
            return this.$auth.user.isSupplier ? 'buyer' : this.$auth.user.isBuyer ? 'supplier' : '';
        }
    },
    breadcrumbs() {
        const items = [];

        items.push(
            {
                title: 'Покупатели',
                link: {
                    name: 'buyer'
                }
            },
            {
                title: 'Моя организация'
            }
        );

        return items;
    }
};
</script>
