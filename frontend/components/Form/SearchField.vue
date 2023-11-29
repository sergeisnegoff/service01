<template>
    <div
        class="select input"
        :class="{
            'error': !!error
        }"
    >
        <label v-if="title" :for="id" class="select-title" v-html="title"></label>
        <BaseSelect
            ref="select"
            v-model="inputValue"
            class="multiselect_field"
            :options="optionsNormalized"
            v-bind="inputPropsCombined"
            v-on="inputListenersCombined"
            @select="$emit('select', $event)"
        >
            <template v-slot:noResult>
                <span v-html="noResult"></span>
            </template>
            <template v-slot:noOptions>
                <span v-html="noOptions"></span>
            </template>
        </BaseSelect>
        <label v-if="error" :for="id" class="invalid-feedback" v-html="error"></label>
    </div>
</template>
<script>
import { debounce } from 'lodash';
import formFieldMixin from '@/mixins/formFieldMixin';
import tooltipSelectMixin from '@/mixins/tooltipSelectMixin';

export default {
    mixins: [formFieldMixin, tooltipSelectMixin],
    props: {
        type: { type: String, default: 'text' },
        options: { type: Array, default: () => [] },
        fetchData: { type: Function },
        noOptions: { type: String, default: 'Начните вводить' },
        noResult: { type: String, default: 'Ничего не найдено' },
        limit: { type: Number, default: 5 },
        reloadOnChange: { type: Boolean, default: false },
        prefetch: { type: Boolean, default: false },
        searchFormatter: {
            type: Function,
            default(item) {
                return {
                    ...item,
                    title: item.value,
                    id: item.value
                };
            }
        }
    },
    data() {
        return {
            isLoading: false,
            requestCancel: null,
            searchResults: []
        };
    },
    computed: {
        inputPropsCombined() {
            return {
                ...this.inputProps,
                ...this.$attrs,
                searchable: true,
                preserveSearch: true,
                internalSearch: false,
                clearOnSelect: false,
                loading: this.isLoading,
                label: 'title',
                trackBy: 'id'
            };
        },
        inputListenersCombined() {
            return {
                'search-change': this.onSearchChange,
                select: (val) => this.setSearchValue(val.title),
                open: this.onOpen,
                ...this.inputListeners
            };
        },
        inputValue: {
            get() {
                return this.value;
            },
            set(value) {
                this.$emit('input', value);
            }
        },
        /**
         * Объединяет дефолтные опции и те что были найдены через поиск
         * @returns {OptionNormalized[]}
         */
        optionsNormalized() {
            /**
             * @type {SearchOption[]}
             */
            const searchResults = this.searchResults;

            /**
             * @type {Option[]}
             */
            const options = this.options;

            return [
                ...options,
                ...searchResults.map((item) => {
                    return this.searchFormatter(item);
                })
            ];
        }
    },
    watch: {
        value(val, prevVal) {
            if (!val && prevVal) this.reset();
        }
    },
    mounted() {
        this.fetchSearchByDefault();
        if (this.inputValue?.title) this.setSearchValue(this.inputValue.title);

    },
    methods: {
        onOpen() {
            const length = this.$refs.select.search.length;

            this.$refs.select.$refs.search.selectionStart = length;
            this.$refs.select.$refs.search.selectionEnd = length;
        },
        reset() {
            this.searchResults = [];
            if (this.$refs.select) this.$refs.select.search = '';

            if (this.debounceSearch.cancel) {
                this.debounceSearch.cancel();
            }

            if (this.requestCancel) {
                this.requestCancel();
            }
        },

        debounceSearch: debounce(function(searchText) {
            this.fetchSearch(searchText);
        }, 300),
        /**
         * Метод для первичной загрузки данных.
         * Нужен чтобы получить необходимые айдишники из Дадаты
         */
        async fetchSearchByDefault() {
            if (!this.prefetch && (!this.reloadOnChange || !this.inputValue)) {
                return;
            }

            let searchValue = this.inputValue;

            try {

                if (typeof searchValue === 'object' && searchValue?.title) {
                    searchValue = searchValue.title;
                }

                await this.fetchSearch(searchValue);

            } catch (error) {
                console.error(error);

                return;
            }

            await this.$nextTick();

            const selectedOption = this.optionsNormalized.find((option) => option.title === searchValue);
            if (selectedOption) {
                this.inputValue = selectedOption;
            }
        },
        async fetchSearch(searchText) {
            this.isLoading = true;

            if (this.fetchData) {
                this.options = await this.fetchData(searchText);
            }

            this.isLoading = false;
        },
        setSearchValue(title) {
            this.$refs.select.search = title;
        },
        onSearchChange(searchText) {
            if (this.requestCancel) {
                this.requestCancel();
            }

            if (!searchText) {
                return;
            }

            this.isLoading = true;
            this.debounceSearch(searchText);
        }
    }
};
</script>
