<template>
    <section class="box__suppliers__page">
        <CompanyInfo v-if="companyMode" :mode="companyMode" />
    </section>
</template>
<script>
export default {
    name: 'SupplierDetailPage',
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
            let mode = 'buyer';

            if (this.$auth.user.isBuyer) {
                mode = 'supplier';
            } else if (this.$auth.user.isModerator) {
                mode = 'moderator';
            }

            return mode;
        }
    },
    breadcrumbs({ store }) {
        const items = [];

        if (!store.$auth.user.isModerator) items.push({
            title: 'Поставщики',
            link: {
                name: 'supplier'
            }
        });

        items.push({
            title: 'О организации'
        });

        return items;
    }
};
</script>
