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
import { buildAddressParams } from '@/helpers/dadata';
import formFieldMixin from '@/mixins/formFieldMixin';

/**
 * @typedef {Object} Option
 * @property {string} title заголовок для показа в селекте
 * @property {string | number} id
 */

/**
 * @typedef {Object} SearchOption
 * @property {string} value текстовое значение найденного элемента
 * @property {string} [unrestricted_value] полное текстовое значение
 * @property {Object} [data]
 * @property {string} data.postal_code индекс
 * @property {string} data.country страна
 * @property {string} data.country_iso_code код страны
 * @property {string} data.region простое название региона
 * @property {string} data.region_type тип региона, сокращённый
 * @property {string} data.region_type_full тип региона, полный
 * @property {string} data.region_with_type название региона с типом
 */

/**
 * @typedef {Option & SearchOption} OptionNormalized
 */

/**
 * @typedef {Function} DadataFormatter
 * @param {SearchOption} item элемент из дадаты
 * @returns {OptionNormalized} отформатированный объект данных для показа
 */

/**
 * ограничения поиска по наименованию
 * @typedef {Object} DadataRestrictionByName
 * @property {string} country страны
 * @property {string} region региона
 * @property {string} city города
 * @property {string} area района
 * @property {string} settlement населенного пункта
 * @property {string} street улицы
 */

/**
 * ограничения поиска по идентификатором
 * @typedef {Object} DadataRestrictionById
 * @property {string} country_iso_code страны
 * @property {string} region_fias_id региона
 * @property {string} city_fias_id города
 * @property {string} street_fias_id улицы
 */

/**
 * ограничения поиска по наименованию
 * @typedef {Object} DadataRestrictionByType
 * @property {string} region_type_full типу региона
 * @property {string} area_type_full района в регионе
 * @property {string} city_type_full города
 * @property {string} settlement_type_full населенного пункта
 * @property {string} street_type_full улицы
 */

/**
 * ограничения поиска на основе данные
 * @typedef {DadataRestrictionByName & DadataRestrictionByType} DadataRestrictionsByData
 */

export default {
    name: 'DadataAddressField',
    mixins: [formFieldMixin],
    props: {
        type: { type: String, default: 'text' },
        options: { type: Array, default: () => [] },
        noOptions: { type: String, default: 'Начните вводить' },
        noResult: { type: String, default: 'Ничего не найдено' },
        limit: { type: Number, default: 5 },
        reloadOnChange: { type: Boolean, default: false },

        /**
         * ограничение типа искомого элемента
         * @type {String}
         */
        bound: {
            type: String,
            required: true,
            validate(value) {
                // country	Страна
                // region	Регион
                // area	    Район
                // city	    Город
                // settlement	Населенный пункт
                // street	Улица
                // house	Дом
                return ['country', 'region', 'area', 'city', 'settlement', 'street', 'house'].includes(value);
            }
        },
        /**
         * @type {DadataRestrictionsByData[]}
         */
        restrictions: { type: Array, default: () => [] },
        /**
         * @type {DadataFormatter}
         */
        dadataFormatter: {
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
                    return this.dadataFormatter(item);
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
            if (!this.reloadOnChange || !this.inputValue) {
                return;
            }

            let searchValue = this.inputValue;

            try {

                if (typeof searchValue === 'object' && searchValue.title) {
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
            // console.log(searchText);
            this.isLoading = true;

            const searchParams = buildAddressParams({
                token: this.$config.DADATA_TOKEN,
                query: searchText,
                limit: this.limit,
                //bound: this.bound,
                bound_from: this.bound,
                bound_to: 'house',
                restrictions: this.restrictions
            });
            const searchResult = await this.$axios.$post(searchParams.url, searchParams.params, {
                ...searchParams.options,
                cancelToken: new this.$axios.CancelToken((cancel) => {
                    this.requestCancel = cancel;
                })
            });

            if (searchResult && searchResult.suggestions) {
                /**
                 * @type {SearchOption[]}
                 */
                this.searchResults = searchResult.suggestions;
            }

            // console.log(searchResult);

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
