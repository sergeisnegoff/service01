import { fetchProductsExport, fetchProductsExportFields } from '@/api/productsExport';
import { download } from '@/helpers/download';

export default {
    fetch() {
        return Promise.all([
            this.fetchProductsExportFields()
        ]);
    },
    data() {
        return {
            formData: null,
            exportFormData: {
                fields: ['nomenclature', 'unit', 'article', 'barcode'],
                all: true,
                productsId: []
            },
            productsExportFields: {
                data: null,
                loading: true
            }
        };
    },
    computed: {
        exportFormDataCompanyId() {
            return this.$auth.user.company.id;
        },
        isAllSelected() {
            const toExportLength = this.exportFormData.productsId.length;

            return this.exportFormData.all ? !toExportLength : toExportLength === this.products.data?.pagination.total;
        },
        selectAllButton() {
            return {
                text: this.isAllSelected ? 'Снять выделение' : 'Выбрать все'
            };
        },
        exportButton() {
            const toExportLength = this.exportFormData.productsId.length;
            const canExport = this.exportFormData.all ? toExportLength !== this.products.data?.pagination.total : !!toExportLength ;

            return {
                isEnabled: !this.sending && canExport
            };
        }
    },
    methods: {
        async fetchProductsExportFields() {
            const data = await fetchProductsExportFields();

            this.productsExportFields = { data, loading: false };
        },
        checkAll() {
            this.exportFormData.all = true;
            this.exportFormData.productsId = [];
        },
        uncheckAll() {
            this.exportFormData.all = false;
            this.exportFormData.productsId = [];
        },
        async exportFields() {
            this.sending = true;

            const data = await fetchProductsExport({
                params: {
                    ...this.exportFormData,
                    companyId: this.exportFormDataCompanyId
                }
            });

            download(data.file);

            this.sending = false;
        },
        selectProduct(id) {
            const findIndex = this.exportFormData.productsId.findIndex(_id => _id === id);

            if (findIndex + 1) this.exportFormData.productsId.splice(findIndex, 1);
            else this.exportFormData.productsId.push(id);
        }
    }
};
