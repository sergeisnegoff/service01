<template>
    <div
        :class="{
            [normalizedVariant.root]: normalizedVariant.root
        }"
    >
        <div
            :is="componentName"
            ref="button"
            class="base-button"
            v-bind="buttonAttrs"
            v-on="$listeners"
        >
            <slot></slot>
        </div>
    </div>
</template>

<script>
import { utilNormalizedVariant, createUtil } from '@/mixins/utilMixins';

export default {
    name: 'BaseButton',
    mixins: [
        utilNormalizedVariant,
        createUtil('theme', 'themes', {
            default: 'primary',
            variants: {
                primary: {
                    root: 'btn'
                },
                link: {
                    root: 'btn-link'
                }
            }
        })
    ],
    props: {
        href: { type: [String, Object] },
        disabled: { type: Boolean, default: false },
        type: {
            type: String,
            default: 'button',
            validate(value) {
                return ['button', 'submit'].includes(value);
            }
        }
    },
    computed: {
        buttonAttrs() {
            const attrs = {
                disabled: this.disabled
            };

            if (this.componentName === 'button') {
                Object.assign(attrs, {
                    type: this.type
                });

            } else if (this.componentName === 'NuxtLink') {
                Object.assign(attrs, {
                    to: this.href
                });

            } else if (this.componentName === 'a') {
                Object.assign(attrs, {
                    href: this.href,
                    target: '_blank'
                });
            }

            return attrs;
        },
        componentName() {
            if (typeof this.href === 'object' || (typeof this.href === 'string' && /^\//.test(this.href))) {
                return 'NuxtLink';
            } else if (typeof this.href === 'string' && !/^\//.test(this.href)) {
                return 'a';
            }

            return 'button';
        }
    },
    methods: {
        focusOnButton() {
            this.$refs.button.focus();
        }
    }
};
</script>
