export default function(url) {
    if (!url) return '';

    let path = url;

    if (!path.endsWith('/')) path += '/';
    if (!path.startsWith('/')) path = '/' + path;

    return url.includes('//') ? url : { path };
}
