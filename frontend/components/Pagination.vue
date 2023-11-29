<template>
    <div v-if="pages > 1" class="box__pagination">
        <ul>
            <li
                :class="{
                    'prev': true,
                    'pointer-events-none': isPrevDisabled
                }"
            >
                <a href="#" @click.prevent="$emit('paginate', currentPage - 1)"><span></span></a>
            </li>
            <li
                v-for="(item, index) in items"
                :key="item.page + index"
                :class="{
                    'active pointer-events-none': item.page === currentPage
                }"
            >
                <a v-if="item.page === 'delimiter'" :key="item.page + index" class="pointer-events-none">
                    ...
                </a>
                <a
                    v-else
                    href="#"
                    @click.prevent="$emit('paginate', item.page)"
                    v-html="item.page"
                ></a>
            </li>
            <li
                :class="{
                    'next': true,
                    'pointer-events-none': isNextDisabled
                }"
            >
                <a href="#" @click.prevent="$emit('paginate', currentPage + 1)"><span></span></a>
            </li>
        </ul>
    </div>
</template>
<script>
export default {
    name: 'Pagination',
    components: {},
    props: {
        // Текущая страница
        page: { type: Number, required: true },
        // Общее количество страниц
        pages: { type: Number, required: true },
        // Сколько ссылок должно быть в главном промежутку
        visible: { type: Number, default: 3 },
        showFirst: { type: Boolean, default: true },
        showLast: { type: Boolean, default: true }
    },
    data() {
        return {
            currentPage: this.page
        };
    },
    computed: {
        items() {
            let pages = [];

            if (this.pages > this.visible + 2) {
                let pageStart = this.currentPage - Math.floor(this.visible / 2);

                if (pageStart < 1) {
                    pageStart = 1;

                } else if (pageStart + this.visible > this.pages) {
                    pageStart = this.pages - this.visible + 1;
                }

                pages = [...Array(this.visible).keys()].map((x, index) => pageStart + index);

                if (this.showFirst && this.currentPage > Math.ceil(this.visible / 2)) {
                    if (this.currentPage > Math.ceil(this.visible / 2) + 1) {
                        pages.splice(0, 0, 'delimiter');
                    }

                    pages.splice(0, 0, 1);
                }

                if (this.showLast && this.currentPage < this.pages - Math.floor(this.visible / 2)) {
                    if (this.currentPage < this.pages - Math.floor(this.visible / 2) - 1) {
                        pages.push('delimiter');
                    }

                    pages.push(this.pages);
                }

            } else {
                pages = [...Array(this.pages + 1).keys()].slice(1);
            }

            return pages.map((page) => ({
                page,
                url: ''
            }));
        },
        isPrevDisabled() {
            return this.currentPage === 1;
        },
        isNextDisabled() {
            return this.currentPage === this.pages;
        }
    },
    watch: {
        ['page'](page) {
            this.currentPage = page;
        }
    },
    methods: {}
};
</script>
