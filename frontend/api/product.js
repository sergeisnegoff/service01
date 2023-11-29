import client from '@/helpers/api';

export function fetchProductManufacturers(config) {
    return client.get('/api/products/self/manufacturers', config);
}
export function addProductManufacturers(data, config) {
    return client.post('/api/products/self/manufacturers', data, config);
}
export function changeProductManufacturers(id, data, config) {
    return client.put(`/api/products/self/manufacturers/${ id }`, data, config);
}
export function deleteProductManufacturers(id, config) {
    return client.delete(`/api/products/self/manufacturers/${ id }`, config);
}

export function fetchProductBrands(config) {
    return client.get('/api/products/self/brands', config);
}
export function addProductBrands(data, config) {
    return client.post('/api/products/self/brands', data, config);
}
export function changeProductBrands(id, data, config) {
    return client.put(`/api/products/self/brands/${ id }`, data, config);
}
export function deleteProductBrands(id, config) {
    return client.delete(`/api/products/self/brands/${ id }`, config);
}

export function fetchProductAllCategories(config) {
    return client.get('/api/products/categories', config);
}
export function fetchProductCategories(config) {
    return client.get('/api/products/self/categories', config);
}
export function addProductCategory(data, config) {
    return client.post('/api/products/self/categories', data, config);
}
export function changeProductCategory(id, data, config) {
    return client.put(`/api/products/self/categories/${ id }`, data, config);
}

export function fetchProducts(config) {
    return client.get('/api/products', config);
}
export function addProduct(data, config) {
    return client.post('/api/products', data, config);
}
export function deleteProduct(id, config) {
    return client.delete(`/api/products/${ id }`, config);
}
export function changeProduct(id, config) {
    return client.put(`/api/products/${ id }`, config);
}
