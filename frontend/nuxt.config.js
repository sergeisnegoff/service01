export default {
    telemetry: false,
    publicRuntimeConfig: {
        DADATA_TOKEN: process.env.DADATA
    },
    build: {
        extend(config) {
            config.module.rules.push({
                test: /\.(ogg|mp3|wav|mpe?g)$/i,
                loader: 'file-loader',
                options: {
                    name: '[path][name].[ext]'
                }
            });
        },
        parallel: true,
        postcss: {
            plugins: {
                'postcss-nested': {

                }
            },
            preset: {
                autoprefixer: {
                    grid: process.env.NODE_ENV === 'production' ? 'autoplace' : false
                }
            }
        }
    },

    /*
    ** Nuxt target
    ** See https://nuxtjs.org/api/configuration-target
    */
    target: 'server',
    /*
    ** Headers of the page
    ** See https://nuxtjs.org/api/configuration-head
    */
    head() {
        const meta = [];

        if (process.server) {
            // meta.push({ hid: 'og:image', name: 'og:image', content: `//${ this.context.req.headers.host }/social.jpg` });
        }

        return {
            title: this.$store.getters['page/metaTitle'] || 'App',
            meta: [
                { charset: 'utf-8' },
                { name: 'viewport', content: 'width=1700' },
                ...this.$store.getters['page/metaInfo'],
                ...meta
            ],
            link: [
                { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
            ]
        };
    },
    /*
    ** Global CSS
    */
    css: [],
    /*
    ** Plugins to load before mounting the App
    ** https://nuxtjs.org/guide/plugins
    */
    plugins: [
        '~/plugins/page.js',
        '~/plugins/axios.js',
        '~/plugins/layer.client.js',
        '~/plugins/breadcrumbs.client.js',
        '~/plugins/breadcrumbs.server.js',
        '~/plugins/tippy.client.js',
        '~/plugins/pluralize.js',
        '~/plugins/storage.js',
        '~/plugins/uuid.js',
        { src: '~/plugins/perfectScrollbar.js', mode: 'client' },
        { src: '~/plugins/flatpickr.client.js', mode: 'client' }
    ],
    /*
    ** Auto import components
    ** See https://nuxtjs.org/api/configuration-components
    */
    components: [
        '~/components',
        { path: '~/components/layers/', global: true }
    ],
    /*
    ** Nuxt.js dev-modules
    */
    buildModules: [
        // Doc: https://github.com/nuxt-community/eslint-module
        '@nuxtjs/eslint-module',
        '@nuxtjs/tailwindcss',
        '@nuxtjs/svg'
    ],
    /*
    ** Nuxt.js modules
    */
    modules: [
        // Doc: https://axios.nuxtjs.org/usage
        '@nuxtjs/axios',
        'nuxt-user-agent',
        '@nuxtjs/auth-next',
        '@nuxtjs/sentry',
        'cookie-universal-nuxt',
        '@nuxtjs/redirect-module'
    ],
    /*
    ** Axios module configuration
    ** See https://axios.nuxtjs.org/options
    */
    axios: {},
    /*
    ** Content module configuration
    ** See https://content.nuxtjs.org/configuration
    */
    content: {},
    /*
    ** Build configuration
    ** See https://nuxtjs.org/api/configuration-build/
    */

    redirect: {
        rules: [
            {
                from: '^(\\/[^\\?]*[^\\/])(\\?.*)?$',
                to: '$1/$2',
                statusCode: 301
            }
        ]
    },

    router: {
        middleware: ['page', 'auth-check'],
        trailingSlash: true,
        prefetchLinks: false
    },

    auth: {
        watchLoggedIn: true,
        resetOnError: true,
        redirect: {
            login: '/login/',
            home: false
        },
        strategies: {
            local: {
                scheme: 'local',
                token: {
                    property: 'token.token',
                    maxAge: 60 * 60 * 24 * 30,
                    type: '',
                    name: 'Token'
                },
                user: {
                    property: false,
                    autoFetch: false
                },
                endpoints: {
                    login: {
                        url: '/api/users/tokens',
                        method: 'post'
                    },
                    logout: {
                        url: '/',
                        method: 'get'
                    },
                    user: {
                        url: '/api/users/self',
                        method: 'get',
                        propertyName: false
                    }
                }
            }
        }
    },

    sentry: {
        dsn: process.env.SSR_SENTRY_DSN || process.env.CLIENT_SENTRY_DSN || '',
        clientConfig: {
            dsn: process.env.CLIENT_SENTRY_DSN || ''
        }
    },

    tailwindcss: {
        exposeConfig: true
    },
    env: {
        mercureSubscribeUrl: process.env.MERCURE_SUBSCRIBE_URL
    }
};
