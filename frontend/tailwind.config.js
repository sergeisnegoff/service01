module.exports = {
    corePlugins: {
        container: false
    },
    theme: {
        extend: {
            fontSize: {
            },
            colors: {
                main: {
                    500: '#5032C8'
                }
            },
            spacing: {
            }
        },
        fontFamily: {
            sans: ['Gerbera', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif']
        },
        screens: {
            xl: { max: '1200px' },
            lg: { max: '1000px' },
            md: { max: '750px' },
            sm: { max: '600px' },
            'a-sm': { min: '601px' },
            'a-md': { min: '751px' },
            'a-lg': { min: '1001px' },
            'a-xl': { min: '1201px' }
        }
    },
    future: {
        removeDeprecatedGapUtilities: true,
        purgeLayersByDefault: true
    },
    purge: {
        enabled: process.env.NODE_ENV === 'production',
        content: [
            './components/**/*.vue',
            './layouts/**/*.vue',
            './pages/**/*.vue',
            './plugins/**/*.js',
            './nuxt.config.js'
        ]
    }
};
