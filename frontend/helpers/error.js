class Error {
    static normalize(_error) {
        if (process.env.NODE_ENV === 'development') {
            console.error(_error);
        }

        if (_error.response && _error.response.data && _error.response.data.error) {
            const error = _error.response.data.error;
            if (error.message) {
                return {
                    message: error.message
                };
            } else if (error.request) {
                const errorNormalize = {};

                for (let key in error.request) {
                    if (Object.prototype.hasOwnProperty.call(error.request, key)) {
                        errorNormalize[key] = error.request[key];
                    }
                }

                return errorNormalize;
            } else {
                if (error.code === 401) {
                    return {
                        message: 'Нет доступа'
                    };
                }
            }
        }

        return {};
    }
}

export default Error;
