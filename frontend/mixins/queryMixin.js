import { isEqual, isEmpty } from 'lodash';
import scrollIntoView from '@/helpers/scrollIntoView';

export default function queryMixin() {
    return {
        data() {
            return {
                page: this.$route.query.page || 1,
                queryItems: {},
                queryMixinOptions: {
                    items: []
                }
            };
        },
        computed: {
            query() {
                return this.$route.query;
            }
        },
        watch(query) {

        },
        created() {
            addWatchers.call(this);
        },
        methods: {
            queryScrollToFirstElement(_ref) {
                let ref = this.$refs[_ref];
                let coords;

                if (Array.isArray(ref)) ref = ref[0];
                if (!ref) return;

                ref = ref.$el || ref;
                coords = ref.getBoundingClientRect();

                if (ref && (coords.y < 0)) {
                    scrollIntoView(ref, { y: -40 });
                }
            }
        }
    };
};

function addWatchers() {
    this.queryMixinOptions.items.forEach(item => {
        if (!item.fieldToWatch) return;

        this.$watch(item.fieldToWatch, function(newValue, oldValue) {
            if (isEmpty(newValue) || isEmpty(oldValue) || isEqual(newValue, oldValue)) return;

            item.fetchApi();
        });
    });
}

function addComputed() {

}


let data = {
    queryMixinOptions: {
        items: [
            {
                name: 'offers',
                fieldToWatch: 'formData',
                limit: 8,
                elementToScroll: 'something',
                exclude: ['smth'],
                fetchApi: () => {}
            }
        ]
    }
}
