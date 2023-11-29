<template>
    <TabLinks :items="menuItems" />
</template>
<script>
import { isUserModificationGranted } from '@/helpers/user';

export default {
    name: 'CompanyNavigation',
    computed: {
        menuItems() {
            const isBuyer = this.$auth.user.isBuyer;

            const nav = [
                {
                    id: 'tabCompanyCommon',
                    title: 'Общие',
                    link: { name: 'company' },
                    active: this.$route.name === 'company'
                }
            ];

            if (this.isUserModificationGranted(this.$auth?.user)) {
                nav.push({
                    id: 'tabCompanyUsers',
                    title: 'Пользователи',
                    link: { name: 'company-users' },
                    active: this.$route.name === 'company-users'
                });
            }

            if (isBuyer) {
                nav.push(
                    {
                        id: 'tabCompanyOrganizations',
                        title: 'Торговые точки',
                        link: { name: 'company-organizations' },
                        active: this.$route.name === 'company-organizations'
                    }
                );
            }

            nav.push({
                id: 'tabCompanyIntegrations',
                title: 'Интеграции',
                link: { name: 'company-integrations' },
                active: this.$route.name === 'company-integrations'
            });

            return nav;
        }
    },
    methods: {
        isUserModificationGranted
    }
};
</script>
