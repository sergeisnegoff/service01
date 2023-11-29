export default function(fileObject) {
    const { file } = fileObject || {};
    if (!file) return null;

    return {
        source: file.url,
        options: {
            type: 'local',
            file,
            metadata: {
                // id нужен для удаления файла
                id: file.id,
                poster: `${ file.url }`
            }
        }
    };
}
