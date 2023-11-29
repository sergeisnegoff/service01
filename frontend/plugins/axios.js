import { setRequestClient } from '~/helpers/api';

// Состояние стора
let isAxiosInited = true;
export default function({ app, $axios }) {
    // Сбрасываем знание об инициализации, чтобы на сервер и клиенте проходила инициализация
    isAxiosInited = false;
    $axios.setBaseURL(process.server ? 'http://varnish' : document.location.origin);

    if (process.client) {
        $axios.setHeader('X-Requested-With', 'XMLHttpRequest');
    }

    app.router.afterEach(function() {
        if (!isAxiosInited) {
            isAxiosInited = true;
            setRequestClient($axios);
        }
    });
}
