<template>
    <PerfectScrollbar
        ref="scroll"
        :options="{
            wheelSpeed: 0.8,
            wheelPropagation: true,
            minScrollbarLength: 20
        }"
        tag="div"
        class="box__content__table"
        :data-customscrollx="fixedColumns"
    >
        <div
            v-if="headers && headers.length"
            class="table__content-title box__table-sorting w-full"
        >
            <div class="row row_nowrap">
                <template v-for="(header, index) in headers">
                    <div
                        v-if="!header.isHidden"
                        :key="index"
                        :class="{
                            ['col-' + header.col]: header.col,
                            col: !header.col,
                            'cursor-pointer': isSortingField(header)
                        }"
                        :style="header.style"
                        @click="$emit('sort', header.field)"
                    >
                        <BaseTooltip
                            class="w-full"
                            placement="top-start"
                            arrow="true"
                        >
                            <template #trigger>
                                <div class="flex">
                                    <h3>
                                        <span
                                            :class="{
                                                'text-main-500': header.sortBy
                                            }"
                                        >
                                            {{ header.title }}
                                        </span>
                                    </h3>
                                    <div
                                        v-if="isSortingField(header)"
                                        class="btn__sorting"
                                        :class="getSortingClass(header)"
                                    >
                                        <button>
                                            <span></span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            {{ header.title }}
                        </BaseTooltip>
                    </div>
                </template>
            </div>
        </div>
        <PerfectScrollbar
            ref="scroll-content"
            :options="{
                wheelSpeed: 0.8,
                wheelPropagation: true,
                minScrollbarLength: 20,
                useBothWheelAxes: false,
                suppressScrollX: true
            }"
            tag="div"
            class="table__content w-full"
            :style="contentStyle"
        >
            <BaseTableItem
                v-for="(item, index) in items"
                :key="item.id || index"
                :class="{
                    '-preloader': item.loading
                }"
                :theme="item.theme"
                :is-edit="item.isEdit"
                @click.native="$emit('item-click', item.raw)"
            >
                <template v-if="!item.isEdit">
                    <slot v-bind="{ item, index, headers }">
                        <template v-for="(field, fieldIndex) in item.fields">
                            <div
                                v-if="!headers[fieldIndex].isHidden"
                                :key="fieldIndex"
                                :class="headers[fieldIndex].col ? 'col-' + headers[fieldIndex].col : 'col'"
                            >
                                {{ field }}
                            </div>
                        </template>
                    </slot>
                    <slot name="actions" v-bind="{ item, index, headers }"></slot>
                </template>
                <slot v-else name="edit" v-bind="{ item, index, headers }"></slot>
            </BaseTableItem>
        </PerfectScrollbar>
    </PerfectScrollbar>
</template>
<script>
export default {
    props: {
        headers: { type: Array },
        items: { type: Array },
        contentStyle: { type: String },
        sortDirection: { type: String },
        sorting: { type: Boolean },
        fixedColumns: { type: Boolean }
    },
    methods: {
        getSortingClass(item) {
            const variants = {
                ASC: 'ascending',
                DESC: 'descending'
            };

            if (!this.sorting || item.notSorting) return '';

            return 'btn__sorting-' + (item.sortBy ? variants[this.sortDirection] : variants.DESC);
        },
        isSortingField(header) {
            return this.sorting && !header.notSorting;
        }
    }
};
</script>
