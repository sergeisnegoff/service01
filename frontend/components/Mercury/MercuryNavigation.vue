<template>
    <TabLinks :items="menuItems" />
</template>

<script>
export default {
    name: 'MercuryNavigation',
    computed: {
        menuItems() {
            return [
                {
                    id: 'mercuryIndex',
                    title: 'Общие',
                    link: { name: 'mercury' },
                    active: this.$route.name === 'mercury'
                },
                {
                    id: 'mercurySettings',
                    title: 'Настройки',
                    link: { name: 'mercury-settings' },
                    active: this.$route.name === 'mercury-settings'
                }
            ];
        }
    }
};
</script>
