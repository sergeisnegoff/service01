export const actions = {
    nuxtServerInit({ dispatch }, { $cookies }) {
        dispatch('page/setModerating', !!$cookies.get('authData'));
    }
};
