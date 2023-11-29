import { Swiper, SwiperSlide } from 'vue-awesome-swiper';
import { generateUuid } from '@/plugins/uuid';
import 'swiper/css/swiper.css';

export default {
    components: { Swiper, SwiperSlide },
    props: {
        id: { type: String, default: () => generateUuid('swiper-slider-') },
        autoplay: { type: Number, default: 0 },
        perSlide: { type: Number, default: 1 },
        perSlideBreakpoints: { type: Object },
        useNavigation: { type: Boolean, default: true },
        usePagination: { type: Boolean, default: false }
    },
    data() {
        return {
            isNavigationVisible: this.items.length > this.perSlide
        };
    },
    computed: {
        swiperOptionsBase() {
            return Object.assign(
                this.perSlideBreakpoints ? {
                    breakpoints: Object.keys(this.perSlideBreakpoints).reduce((result, breakWidth) => {
                        const amount = this.perSlideBreakpoints[breakWidth];

                        result[breakWidth] = {
                            slidesPerView: amount,
                            slidesPerGroup: amount
                        };

                        return result;
                    }, {})
                } : {
                    slidesPerView: this.perSlide,
                    slidesPerGroup: this.perSlide
                },
                {
                    loop: false,
                    autoplay: this.autoplay > 0 ? {
                        delay: this.autoplay
                    } : false,
                    spaceBetween: 15
                },

                this.useNavigation ? {
                    navigation: {
                        nextEl: `#${ this.id }-next`,
                        prevEl: `#${ this.id }-prev`,
                        disabledClass: 'is-navigation-disabled',
                        hiddenClass: 'is-navigation-hidden'
                    }
                } : null,

                this.usePagination ? {
                    pagination: {
                        el: `#${ this.id }-pagination`,
                        clickable: true
                    }
                } : null,

                this.thumbs ? {
                    thumbs: {
                        swiper: this.swiperThumbsInstance
                    }
                } : null
            );
        },
        swiperInstance() {
            return this.$refs.swiper && this.$refs.swiper.swiperInstance;
        },
        swiperThumbsInstance() {
            return this.$refs.thumbs && this.$refs.thumbs.swiperInstance;
        }
    },
    methods: {
        onSwiperReady() {
            this.updateNavigation();
        },
        updateNavigation() {
            if (!this.swiperInstance) return;

            this.$nextTick(() => {
                this.swiperInstance.navigation.destroy();
                this.swiperInstance.navigation.init();
                this.swiperInstance.navigation.update();
                this.isNavigationVisible = this.items.length > (this.perSlideBreakpoints ? this.perSlideBreakpoints[this.swiperInstance.currentBreakpoint] : this.perSlide);
            });
        },
        onSwiperRezize() {
            if (!this.swiperInstance) return;

            this.$nextTick(() => {
                this.isNavigationVisible = this.items.length > (this.perSlideBreakpoints ? this.perSlideBreakpoints[this.swiperInstance.currentBreakpoint] : this.perSlide);
            });
        }
    }
};
