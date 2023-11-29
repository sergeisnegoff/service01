<template>
    <div class="overflow-hidden">
        <div
            class="tabs"
            :class="{
                ['tabs_size_' + size]: size,
                'tabs_line_hide': !showLine
            }"
        >
            <div class="tabs__navigator">
                <ul ref="tabs" class="tabs__navigator-menu -scroll">
                    <li
                        v-for="tab in tabs"
                        :key="tab.settings.id"
                        ref="tab"
                        class="tabs__navigator-item"
                        :data-id="tab.settings.id"
                        :class="{
                            'is-active': tab.settings.id === selectedId
                        }"
                    >
                        <a
                            href="javascript:void(0)"
                            class="tabs-item"
                            :class="[
                                { 'is-active': tab.settings.id === selectedId },
                                { 'is-error': hasErrors.includes(tab.settings.id) }
                            ]"
                            @click.prevent="selectTab(tab.settings.id)"
                        >
                            <span
                                class="tabs-item__title tracking-wider"
                                :class="[
                                    { 'text-error': hasErrors.includes(tab.settings.id) }
                                ]"
                            >
                                {{ tab.settings.title }}
                                <span
                                    v-if="tab.settings.side"
                                    :class="{
                                        'text-main text-opacity-40': tab.settings.id !== selectedId
                                    }"
                                >
                                    {{ tab.settings.side }}
                                </span>
                            </span>
                        </a>
                    </li>
                </ul>
                <slot name="side"></slot>
            </div>
        </div>
        <div class="tabs__content">
            <slot :selectedId="selectedId"></slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'Tabs',
    props: {
        size: { type: String },
        tabsData: { // когда от табов требуется только навигация
            type: Array
        },
        showOrder: {
            type: Boolean,
            default: true
        },
        activeTabId: {
            type: String
        },
        hasErrors: {
            type: Array,
            default: () => []
        },
        showLine: { type: Boolean, default: true }
    },
    data: () => ({
        tabs: [],
        tabsItems: []
    }),
    computed: {
        selectedId() {
            return this.activeTabId;
        }
    },
    watch: {
        activeTabId() {
            if (this.$refs.tab) {
                let activeTab = this.$refs.tab.filter((item) => {
                    return this.activeTabId === item.dataset.id;
                })[0];

                this.$refs.tabs.scrollTo({
                    top: 0,
                    left: activeTab.offsetLeft - 30,
                    behavior: 'smooth'
                });
            }
        }
    },
    created() {
        if (this.tabsData && this.tabsData.length) {
            this.tabs = this.tabsData;
        } else {
            this.tabsItems = this.$children;
            this.$nextTick(() => {
                const filteredTabs = this.tabsItems.filter(item => item.$options.name === 'Tab');
                const tabsIds = filteredTabs.map(item => item.settings.id);
                const uniqueTabsId = [...new Set(tabsIds)];
                let sanitizedTabs = [];
                uniqueTabsId.forEach(id => {
                    sanitizedTabs.push(filteredTabs.filter(item => item.settings.id === id)[0]);
                });

                this.tabs = sanitizedTabs;
            });
        }
    },
    methods: {
        selectTab(id) {
            this.$emit('selectTab', id);
            /* this.tabs.forEach(tab => {
                tab.selected = tab.settings.id === iwd;
            });*/
        }
    }
};
</script>
