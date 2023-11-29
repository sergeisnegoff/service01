import Vue from 'vue';

import './layer.css';

/* eslint-disable */

/**
 * Возвращает опции компонента
 * @param component {string|object|function}
 * @returns {Promise<*>}
 */
async function resolveComponentOptions(component) {
    if (typeof component === 'object') {
        return component;
    }

    if (typeof component === 'function') {
        return component.options || await component.call();
    }

    if (typeof component === 'string') {
        if (Vue.options.components[component]) {
            return await resolveComponentOptions(Vue.options.components[component]);
        }
    }

    throw new Error('Layer component not found: ' + component.toString());
}

/**
 * Генерирует ключ для слоя
 * @param key {string|array}
 * @returns {string}
 */
function buildKey(key) {
    if (typeof key === 'string') {
        return key;
    }

    return key.filter(x => !!x).join('-');
}

/**
 * Возвращает результата ответ при закрытии слоя.
 * Если в компоненте слоя определена функция resolve, вызывает ее для получения ответа.
 * Функция будет вызывана только в том случае, если при закрытии не был передан результат ответа (result).
 * @param layer
 * @param result
 * @returns {Promise<*>}
 */
async function resolveClose(layer, result) {
    const layerInstance = this.$refs['layer-' + layer.key];

    if (typeof result === 'undefined') {
        if (typeof layer.options.resolve === 'function') {
            result = await layer.options.resolve.call(layerInstance);
        }
    }

    layer.resolve(result);

    return result;
}

/**
 * Найти индекс слоя в стаке по ключу
 * @param key
 */
function findLayerIndex(key) {
    for (let i in this.layers) {
        if (this.layers[i].key === key) {
            return i;
        }
    }

    throw new Error('Layer not found: ' + key)
}

/**
 * Найти слой по ключу
 * @param key
 * @returns {T}
 */
function findLayer(key) {
    const layer = this.layers.find(l => l.key === key);
    if (!layer) {
        throw new Error('Layer not found: ' + key);
    }

    return layer;
}

/**
 * Делает слой фронтальным
 * @param layer
 * @param props
 * @returns {Promise<*>}
 */
async function focusLayer(layer, props) {
    this.layers.splice(findLayerIndex.call(this, layer.key), 1);
    this.layers.push(layer);

    layer.props = props;

    return layer.promise;
}

/**
 * Закрыть фронтальный слой
 * @returns {Promise<*>}
 */
async function closeFrontLayer() {
    if (!this.layers.length) {
        return;
    }

    const layer = this.layers[this.layers.length - 1];

    if (layer.options.locked) {
        return;
    }

    return await this.close(this.layers[this.layers.length - 1].key);
}

Vue.use({
    install() {
        window.onNuxtReady((app) => {
            const options = {};
            for (let k in app.$options) {
                if (app.$options.hasOwnProperty(k)) {
                    if (k.match(/^\$|^(router|store)$/)) {
                        options[k] = app.$options[k];
                    }
                }
            }

            // Счетчик для добавления суффикса к ключу слоя
            let keyCounter = 0;

            // Смещение прокрутки до открытия первого слоя
            let appScrollOffset;

            // Контейнер приложения
            const appElement = app.$el;
            const bodyElement = document.querySelector('body');

            /**
             * Хук срабатыаем при анимании закрытия слоя
             * @param el
             */
            function transitionLeave(el) {
                // Пропускаем элемент блекаута при обработки анимации
                if (el.classList.contains('layer-blackout')) {
                    return;
                }

                if (this.layers.length) {
                    // При закрытии восстанвливает смещение прокрутки предыдущего слоя
                    this.$nextTick(() => {
                        document.documentElement.scrollTop = this.layers[this.layers.length - 1].offset;
                    });

                } else {
                    // При закрытии последего слоя разблокирует приложение и восстанавливает смещение прокрутки
                    appElement.style.top = null;
                    appElement.classList.remove('is-layer-locked');
                    bodyElement.classList.remove('overflow-y-auto');
                    document.documentElement.scrollTop = appScrollOffset;
                }
            }

            /**
             * Хук срабатывает перед началом анимации закрытия слоя
             * @param el
             */
            function transitionBeforeLeave(el) {
                // Пропускаем элемент блекаута при обработки анимации
                if (el.classList.contains('layer-blackout')) {
                    return;
                }

                // Смещает позицию анимируемого слоя на значение смещения прокрутки
                el.style.top = -document.documentElement.scrollTop + 'px';
            }

            const $layer = new Vue({
                ...options,

                data() {
                    return {
                        // Стак всех слоев
                        layers: [],
                    }
                },
                computed: {
                    // Размер стака слоев, используется для управления анимацией
                    layersStackSize() {
                        return this.layers.length;
                    }
                },
                watch: {
                    layersStackSize(size, prevSize) {
                        if (prevSize < 1 && size > 0) {
                            // При открытии первого слоя блокирует приложение и запоминает смещения прокрутки
                            appScrollOffset = document.documentElement.scrollTop;
                            appElement.style.top = -appScrollOffset + 'px';
                            appElement.classList.add('is-layer-locked');
                            bodyElement.classList.add('overflow-y-auto');
                            document.documentElement.scrollTop = 0;

                        } else if (size > prevSize && size > 1) {
                            // При открытии последующих слоев запоминает смещение прокрутки для предыдущего слоя
                            if (document.documentElement.scrollTop) {
                                this.layers[this.layers.length - 2].offset = document.documentElement.scrollTop;
                            }
                        }
                    }
                },
                render(h) {
                    const layers = [];

                    this.layers.forEach((l, i) => {
                        layers.push(h(l.component, {
                            key: l.key,
                            ref: 'layer-' + l.key,
                            props: l.props,
                            style: {
                                top: (l.offset && i < this.layersStackSize - 1) ? -l.offset + 'px' : null,
                            },
                            class: {
                                'layer_hidden': i < this.layersStackSize - 3,
                                'layer_back': i < this.layersStackSize - 1,
                                'layer_front': i >= this.layersStackSize - 1,
                            },
                            on: {
                                close(result) {
                                    $layer.close(l.key, result);
                                }
                            }
                        }));
                    });

                    // Добавляет элемент блекаута перед фронтальным слоем
                    layers.splice(layers.length ? layers.length - 1 : 0, 0, [
                        h(
                            'div',
                            {
                                class: {
                                    'layer-blackout': true,
                                    'layer-blackout_hidden': !layers.length
                                },
                                on: {
                                    click: closeFrontLayer.bind($layer)
                                },
                                key: 'layer-blackout'
                            }
                        )
                    ]);

                    return h(
                        'transition-group',
                        {
                            class: ['layers-container'],
                            on: {
                                'leave': transitionLeave.bind(this),
                                'before-leave': transitionBeforeLeave.bind(this),
                            },
                            props: {
                                tag: 'div',
                                name: 'layer'
                            }
                        },
                        layers
                    );
                },
                methods: {
                    alert(props) {
                        return this.open('AlertLayer', props);
                    },

                    confirm(props) {
                        return this.open('ConfirmLayer', props);
                    },

                    /**
                     * Открыть слой компонента
                     * @param component Компонент
                     * @param props Входные параметры компонента
                     * @returns {Promise<*>}
                     */
                    async open(component, props = {}) {
                        const options = (await resolveComponentOptions(component) || {}).layer || {};
                        const layerName = (typeof component === 'string') ? component : options.name || 'layer';
                        const key = buildKey(props.key || [layerName, options.single ? null : ++keyCounter]);

                        try {
                            // Если слой с таким ключем уже присутствует, делает его фронтальным
                            const layer = findLayer.call(this, key);
                            return focusLayer.call(this, layer, props);

                        } catch (e) {
                            //
                        }

                        let resolve;
                        const promise = new Promise(r => resolve = r);

                        let timeoutTimer;
                        const timeout = props.timeout || options.timeout;
                        if (timeout) {
                            timeoutTimer = setTimeout(() => this.close(key), timeout);
                        }

                        this.layers.push({
                            key,
                            component,
                            props,
                            options,
                            promise,
                            resolve,
                            timeoutTimer
                        });

                        return promise;
                    },

                    /**
                     * Закрыть слой по ключу
                     * @param key Ключ слоя
                     * @param result Возвращаемое значение ответа
                     * @returns {Promise<*>}
                     */
                    async close(key, result) {
                        if (!this.layers.length) {
                            return;
                        }

                        const layer = findLayer.call(this, key);
                        this.layers = this.layers.filter(l => l.key !== key);

                        clearTimeout(layer.timeoutTimer);

                        return await resolveClose.call(this, layer, result);
                    },

                    /**
                     * Закрыть все слои
                     * @returns {Promise<[*]>}
                     */
                    closeAll() {
                        const promise = Promise.all(this.layers.map(l => resolveClose.call(this, l)));
                        this.layers = [];

                        return promise;
                    }
                }
            });

            Vue.prototype.$layer = $layer;

            document.body.appendChild($layer.$mount().$el);
        });
    }
});
