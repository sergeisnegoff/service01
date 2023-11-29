const defaultItems = [];

export function state() {
    return {
        items: defaultItems.slice(0)
    };
}

export const mutations = {
    reset: state => state.items = [],
    addItem: (state, item) => state.items.push(item),
    addItems: (state, items) => state.items = state.items.concat(items)
};
