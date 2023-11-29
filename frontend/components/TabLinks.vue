<template>
    <div class="box__tabs">
        <ul>
            <li
                v-for="item in items"
                :key="item.id"
                :class="{
                    active: item.active || (activeId !== undefined && item.id === activeId)
                }"
            >
                <NuxtLink v-if="item.link" :to="item.link" v-html="item.title" />
                <a v-else href="#" @click.prevent="onTabClick(item)" v-html="item.title"></a>
            </li>
        </ul>
    </div>
</template>
<script>
export default {
    name: 'TabLinks',
    components: {},
    props: {
        items: {
            type: Array
        },
        activeId: {
            type: [String, Number]
        }
    },
    data() {
        return {};
    },
    computed: {},
    methods: {
        onTabClick(item = {}) {
            this.$emit('select', item);
        }
    }
};
</script>
