export default function sleep(ms) {
    return (function() {
        let timer;
        let resolvePromise;
        let resolved = false;

        const promise = new Promise(resolve => {
            resolvePromise = resolve;
            timer = setTimeout(() => {
                resolved = true;
                resolve();
            }, ms);
        });

        promise.abort = function() {
            clearInterval(timer);

            if (!resolved) {
                resolvePromise();
            }
        };

        return promise;
    })();
}
