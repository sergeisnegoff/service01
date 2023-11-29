export default function getImage(imageUrl, options) {
    imageUrl = imageUrl.match(/^https?:\/\//) ? imageUrl : ('local://' + imageUrl);

    return [
        '/_image',
        'i',
        options,
        Buffer.from(imageUrl)
            .toString('base64')
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=+$/, '')
    ].join('/');
}
