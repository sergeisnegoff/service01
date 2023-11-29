<template>
    <div class="box__input-file">
        <FilePond
            v-model="files"
            :files="files"
            v-bind="$attrs"
            :accepted-file-types="accept"
            :allow-multiple="multiple"
            :label-idle="label"
            v-on="{
                removefile: onRemoveFile
            }"
        />
        <div v-if="error" class="field__error text-red-500" v-html="error"></div>
    </div>
</template>
<script>
import vueFilePond from 'vue-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import FilePondPluginFilePoster from 'filepond-plugin-file-poster';
import 'filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css';


const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginImagePreview,
    FilePondPluginFilePoster
);

export default {
    name: 'FileField',
    components: {
        FilePond
    },
    props: {
        value: {
            type: Array,
            default: () => ([])
        },
        multiple: {
            type: Boolean,
            default: false
        },
        accept: {
            type: String,
            default: 'image/jpeg, image/png'
        },
        label: {
            type: String
        },
        error: {
            type: String
        }
    },
    data() {
        return {};
    },
    computed: {
        files: {
            get() {
                return this.value;
            },
            set(value) {
                this.$emit('input', value.map(fileWrapper => fileWrapper.file));
            }
        }
    },
    methods: {
        onRemoveFile(data, fileData) {
            const metaData = fileData.getMetadata && fileData.getMetadata() || {};
            const fileId = metaData.id;
            if (!fileId) return;

            this.$emit('removedItem', fileId);
        }
    }
};
</script>
