<template>
    <div v-if="isNeedShow" class="box__breadcrumbs">
        <ul>
            <li
                v-for="item in itemsNormalized"
                :key="item.id"
            >
                <span v-if="!item.link">{{ item.title }}</span>
                <NuxtLink v-else :to="item.link" :title="item.title">
                    {{ item.title }}
                </NuxtLink>
            </li>
        </ul>
    </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
    name: 'Breadcrumbs',
    computed: {
        ...mapState('breadcrumbs', ['items']),

        isNeedShow() {
            return this.itemsNormalized.length > 0;
        },
        itemsNormalized() {
            const items = [];
            const excludeRoleRoutes = ['company-list'];
            const { isSupplier } = (this.$auth.user || {});
            const { isBuyer } = (this.$auth.user || {});
            const { isModerator } = (this.$auth.user || {});

            if (!excludeRoleRoutes.includes(this.$route.name)) {
                if (isSupplier) {
                    items.push({
                        title: 'Поставщик',
                        link: {
                            name: 'company'
                        }
                    });
                } else if (isBuyer) {
                    items.push({
                        title: 'Покупатель',
                        link: {
                            name: 'company'
                        }
                    });
                } else if (isModerator) {
                    items.push({
                        title: 'Модератор',
                        link: {
                            name: 'requests'
                        }
                    });
                }
            }

            return [
                ...items,
                ...(this.items || [])
            ];
        }
    }
};
</script>
