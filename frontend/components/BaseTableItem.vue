<template>
    <div
        class="box__table__item w-full"
        :class="{
            [themeResult]: theme
        }"
    >
        <div
            ref="item"
            class="box__item w-full"
            :class="{
                'box__item-edit': isEdit
            }"
            data-table
        >
            <div
                ref="row"
                class="row row_nowrap"
            >
                <slot></slot>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        isEdit: { type: Boolean },
        theme: { type: String }
    },
    data: () => ({
        width: null
    }),
    computed: {
        themeResult() {
            const themes = {
                warning: 'table__item-warning'
            };

            return themes[this.theme] || '';
        }
    }
};
</script>
