<template>
    <div></div>
</template>

<script>

export default {
    layout: 'default',
    asyncData({ redirect }) {
        redirect({ name: 'company-list' });
    }
};
</script>

