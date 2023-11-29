<template>
    <div class="box__slider">
        <div v-if="!thumbs || (thumbs && isThumbsReady)" class="swiper-container gallery-top">
            <Swiper
                ref="swiper"
                class="swiper-wrapper"
                :options="swiperOptions"
            >
                <SwiperSlide
                    v-for="(item, itemIndex) in items"
                    :key="'slide' + itemIndex"
                    class="swiper-slide"
                    :style="{backgroundImage: `url(${ item })`}"
                >
                    <slot
                        name="item"
                        v-bind="{ item, itemIndex }"
                    ></slot>
                </SwiperSlide>
            </Swiper>
            <!-- Add Arrows -->
            <div :id="swiperOptions.navigation.nextEl.slice(1)" class="swiper-button-next swiper-button-white"></div>
            <div :id="swiperOptions.navigation.prevEl.slice(1)" class="swiper-button-prev swiper-button-white"></div>
        </div>
        <div
            v-if="thumbs"
            class="swiper-container gallery-thumbs"
        >
            <Swiper
                ref="thumbs"
                :options="swiperThumbsOptions"
                class="swiper-wrapper"
                @ready="onSwiperThumbsReady"
            >
                <SwiperSlide
                    v-for="(item, itemIndex) in items"
                    :key="'thumb' + itemIndex"
                    class="swiper-slide"
                    :style="{backgroundImage: `url(${ item })`}"
                >
                    <slot name="thumbItem" v-bind="{ item, itemIndex }"></slot>
                </SwiperSlide>
            </Swiper>
        </div>
    </div>
</template>
<script>
import swiperMixin from '@/mixins/swiperMixin';

export default {
    name: 'Slider',
    components: {},
    mixins: [swiperMixin],
    props: {
        items: { type: Array, required: true },
        thumbs: { type: Boolean, default: true }
    },
    data() {
        return {
            isThumbsReady: false
        };
    },
    computed: {
        swiperOptions() {
            return Object.assign(
                {},
                this.swiperOptionsBase,
                this.swiperOptionsBase.pagination ? {
                    pagination: {
                        ...this.swiperOptionsBase.pagination,
                        bulletClass: 'things-slider__pagination-item',
                        bulletActiveClass: 'is-pagination-active'
                    }
                } : null
            );
        },
        swiperThumbsOptions() {
            return {
                spaceBetween: 15,
                slidesPerView: 3,
                freeMode: true,
                watchSlidesVisibility: true,
                watchSlidesProgress: true
            };
        }
    },
    methods: {
        onSwiperThumbsReady() {
            this.isThumbsReady = true;
        }
    }
};
</script>
