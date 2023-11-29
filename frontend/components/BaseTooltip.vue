<template>
    <ClientOnly>
        <Tippy
            v-bind="$attrs"
            :show-on-create="true"
            class="tooltip"
            :interactive="true"
            @init="onInit"
            @show="onShow"
            @hide="onHide"
        >
            <template #trigger>
                <slot name="trigger" :show="show"></slot>
            </template>
            <slot></slot>
        </Tippy>
    </ClientOnly>
</template>

<script lang="ts">
export default {
    props: {
        showOnCreate: {
            type: Boolean
        }
    },
    data() {
        return {
            show: false
        };
    },
    methods: {
        onInit(instance) {
            if (this.showOnCreate) {
                instance.show();
                this.show = true;
            }
        },
        onShow() {
            this.$emit('show');
            this.show = true;
        },
        onHide() {
            this.$emit('hide');
            this.show = false;
        }
    }
};
</script>
<style lang="postcss">
.tooltip {
    & > div,
    & > div > div {
        @apply cursor-pointer;
    }
}
.tippy-popper {
    pointer-events: auto;
    max-width: none;
}
.tippy-popper[x-placement^=right] [data-animation=shift-away][data-state=visible] {
    @apply transform translate-x-1;
}
.tippy-popper[x-placement^=right] {
    .tippy-tooltip {
        @apply bottom-2.5 transform;
    }
    .tippy-arrow {
        @apply transform translate-y-2.5;
        border-right: 4px solid;
        border-top: 4px solid transparent;
        border-bottom: 4px solid transparent;
        left: -4px;
    }
    .tippy-arrow {
        &::before {
            @apply rotate-45;
            left: -2px;
            top: 2px;
        }
        &::after {
            @apply -rotate-45;
            left: -2px;
            top: -2px;
        }
    }
}

.tippy-tooltip {
    background-color: rgb(118, 117, 135);
    .tippy-arrow {
        border-top-color: rgb(118, 117, 135)!important;
        transform: translateX(-50%);
        left: 50%!important;
        &::before {
            @apply absolute w-1.5 h-px transform;
            content: '';
        }
        &::after {
            @apply absolute w-1.5 h-px transform;
            content: '';
        }
    }
    .tippy-content {
        @apply text-left;
    }
}

.tippy-tooltip.light-theme,
.tippy-tooltip.light-bordered-theme {
    @apply bg-white shadow-md text-black;

    .tippy-backdrop {
        @apply bg-white;
    }
}
</style>
