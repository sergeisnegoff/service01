<template>
    <div>
        <ExportPageTemplate :categories-api="fetchProductAllCategories" />
    </div>
</template>
<script>
import { fetchProductAllCategories } from '@/api/product';

export default {
    name: 'SupplierGoodsPage',
    methods: {
        fetchProductAllCategories
    },
    breadcrumbs({ store, route }) {
        const items = [];

        if (!store.$auth.user.isModerator) items.push({
            title: 'Поставщики',
            link: {
                name: 'supplier'
            }
        });

        items.push(
            {
                title: 'Товары',
                link: { name: 'supplier-id-goods', params: { id: route.params.id  } }
            },
            {
                title: 'Экспорт товаров'
            }
        );

        return items;
    }
};
</script>
