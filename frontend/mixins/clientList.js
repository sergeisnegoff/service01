export default {
    data() {
        return {
            list: {
                items: [],
                pagination: {
                    page: 0,
                    pages: 0
                },
                limit: 6,
                loading: false,
                cancel: null
            },
            formData: {
                query: ''
            }
        };
    },
    methods: {
        async fetchData() {
            const page = this.list.pagination && this.list.pagination.page || 1;
            const { limit } = this.list;

            return this.fetchListApi({
                cancelToken: new this.$axios.CancelToken((cancel) => {
                    this.list.cancel = cancel;
                    this.list.loading = true;
                }),
                params: {
                    ...this.formData,
                    page,
                    limit
                },
                progress: false
            })
                .then(response => {
                    this.list.items = response.items;
                    this.list.pagination = response.pagination;
                })
                .finally(() => {
                    this.list.cancel = null;
                    this.list.loading = false;
                });
        },
        async toggleFavorite(state, companyId) {
            if (state) {
                await this.addToFavorite(companyId);
            } else {
                await this.removeFromFavorite(companyId);
            }

            await this.fetchData();
        },
        onTabChange(tabItem) {
            if (!tabItem || !tabItem.id) return;
            const activeTabid = tabItem.id;
            (Array.isArray(this.tabs) ? this.tabs : []).forEach(tab => {
                const isActive = tab.id === activeTabid;
                tab.active = isActive;

                if (tab.formPropName) {
                    this.formData[tab.formPropName] = isActive;
                }
            });

            this.activeTab = activeTabid;
            this.formData.query = '';
            this.fetchData();
        },
        setBreadcrumbs() {
            this.$store.dispatch('breadcrumbs/update', [
                {
                    title: 'Покупатели'
                }
            ]);
        },
        onPaginate(page) {
            this.list.pagination.page = page;
            this.fetchData();
        },
        onSearch() {
            this.list.pagination.page = 1;
            this.fetchData();
        }
    }
};
