<script>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import './styles.css';

export default {
    name: 'BaseSelect',
    extends: Multiselect,
    props: {
        searchable: {
            type: Boolean,
            default: false
        },
        showLabels: {
            type: Boolean,
            default: false
        },
        deselectLabel: {
            type: String,
            default: '[Enter] чтобы удалить'
        },
        placeholder: {
            type: String,
            default: 'Выберите вариант'
        },
        allowEmpty: {
            type: Boolean,
            default: false
        },
        multiple: {
            type: Boolean,
            default: false
        }
    }
};
</script>
