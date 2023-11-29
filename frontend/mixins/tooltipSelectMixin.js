export default {
    props: {
        tooltip: { type: Boolean, default: false }
    },
    data() {
        return {
            observer: null,
            table: null
        };
    },
    mounted() {
        if (!this.tooltip) return;

        this.table = this.$el.closest('.box__content__table');

        if (this.table) {
            this.table.addEventListener('scroll', this.setCoords);
            this.observer = new ResizeObserver(this.setCoords);
            this.observer.observe(document.querySelector('.box__app__content'));
            window.addEventListener('resize', this.setCoords);
        }
    },
    destroyed() {
        if (this.table) {
            const el = document.querySelector(`[js-multiselect="${ this.id }"]`);

            if (el) el.remove();

            this.observer.disconnect();
            window.removeEventListener('resize', this.setCoords);
        }
    },
    methods: {
        async setCoords() {
            if (!this.table || !this.$refs.select.isOpen) return;

            await this.$nextTick();

            const el = this.$refs.select.$el;
            const ps = document.querySelector('#main-scroll');
            const psCoords = ps.getBoundingClientRect();
            const coords = el.getBoundingClientRect();
            const coordsX = coords.x - psCoords.x;
            let coordsY = coords.y - psCoords.y;
            let wrapper = document.querySelector(`[js-multiselect="${ this.id }"]`);
            let items = el.querySelector('.multiselect__content-wrapper');

            if (!wrapper) {
                wrapper = document.createElement('div');
                wrapper.setAttribute('js-multiselect', this.id);

                wrapper.style.zIndex = 10001;
                wrapper.style.position = 'absolute';
                items.style.position = 'static';

                wrapper.append(items);
                ps.prepend(wrapper);
            } else {
                items = wrapper.querySelector('.multiselect__content-wrapper');
            }

            wrapper.classList.remove('hidden');

            coordsY = el.classList.contains('multiselect--above')
                ? coordsY - wrapper.offsetHeight + ps.scrollTop
                : coordsY + el.offsetHeight + ps.scrollTop;

            wrapper.classList = el.classList;
            wrapper.style.top = coordsY + 'px';
            wrapper.style.left = coordsX + 'px';
            wrapper.style.width = el.offsetWidth + 'px';
            wrapper.style.minWidth = 255 + 'px';
        },
        onClose() {
            const el = document.querySelector(`[js-multiselect="${ this.id }"]`);

            if (el) el.classList.add('hidden');
        }
    }
};
