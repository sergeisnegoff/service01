<template>
    <div
        :class="[
            'layer layer_base my-4',
            {
                ['layer_width_' + width]: width
            }
        ]"
    >
        <div class="layer__wrapper">
            <template v-if="'close' in $slots">
                <div class="layer__close">
                    <slot name="close"></slot>
                </div>
            </template>
            <div class="layer__container">
                <div class="layer__inner">
                    <template v-if="'main' in $slots">
                        <div class="layer__main">
                            <slot name="main"></slot>
                        </div>
                    </template>
                    <template v-if="'bottom' in $slots">
                        <div class="layer__bottom">
                            <slot name="bottom"></slot>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BaseLayer',
    props: {
        width: {
            type: String,
            default: ''
        }
    }
};
</script>
