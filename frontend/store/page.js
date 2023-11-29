export function state() {
    return {
        data: {},
        raw: {},
        isModerating: false
    };
}

export const getters = {
    title: state => state.data.id ? state.data.title : '',
    metaTitle: state => state.data.id ? (state.data.meta.title || state.data.title) : '',
    metaInfo: state => {
        if (!state.data.id) {
            return [];
        }

        return [
            { hid: 'description', name: 'description', content: state.data.meta.description || '' },
            { hid: 'keywords', name: 'keywords', content: state.data.meta.keywords || '' },
            { hid: 'og:title', name: 'og:title', content: state.data.meta.title || '' },
            { hid: 'og:description', name: 'og:description', content: (state.data.meta.description || '').replace(/<\/?[^>]+(>|$)/g, '') }
        ];
    }
};

export const mutations = {
    update(state, payload) {
        state.raw = payload;
    },
    reset(state) {
        state.raw = {};
    },
    fix(state) {
        state.data = state.raw;
    },
    setModerating(state, payload) {
        state.isModerating = payload;
    }
};

export const actions = {
    reset({ commit }) {
        commit('reset');
    },
    update({ commit }, payload) {
        commit('update', payload);
    },
    setModerating({ commit }, payload) {
        commit('setModerating', payload);
    }
};
