<template>
    <TabLinks :items="navigationItems" />
</template>
<script>
export default {
    name: 'ProductNavigation',
    computed: {
        navigationItems() {
            return [
                {
                    id: 'tabProduct',
                    title: 'Товары',
                    link: { name: 'products' },
                    active: this.$route.name === 'products'
                },
                {
                    id: 'tabProductSuppliers',
                    title: 'Производители',
                    link: { name: 'products-suppliers' },
                    active: this.$route.name === 'products-suppliers'
                },
                {
                    id: 'tabProductBrands',
                    title: 'Бренды',
                    link: { name: 'products-brands' },
                    active: this.$route.name === 'products-brands'
                }
            ];
        }
    }
};
</script>
