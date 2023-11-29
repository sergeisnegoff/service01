import { companyFavoriteAdd, companyFavoriteRemove } from '@/api/company';

export default {
    methods: {
        addToFavorite(companyId) {
            try {
                return companyFavoriteAdd({
                    id: companyId
                });
            } catch (e) {
                console.log('Unable to add to favorite', e);
            }
        },
        removeFromFavorite(companyId) {
            try {
                return companyFavoriteRemove({
                    params: {
                        id: companyId
                    }
                });
            } catch (e) {
                console.log('Unable to add to favorite', e);
            }
        },
        async toggleFavorite(state, companyId) {
            if (state) {
                await this.addToFavorite(companyId);
            } else {
                await this.removeFromFavorite(companyId);
            }

            if (this.fetchData && typeof this.fetchData === 'function') await this.fetchData();
        }
    }
};
