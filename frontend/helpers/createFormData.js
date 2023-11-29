function getType(any) {
    return Object.prototype.toString.call(any).slice(8, -1);
}

function toName(path) {
    let array = path.map((part) => `[${ part }]`);
    array[0] = path[0];

    return array.join('');
}

function appendToForm(form, path, node, filename) {
    let name = toName(path);

    if (typeof filename == 'undefined') {
        form.append(name, node);
    } else {
        form.append(name, node, filename);
    }
}

function check(node) {
    switch (getType(node)) {
    case 'Array':
        return true;
    case 'Object':
        return true;
    case 'FileList':
        return true;
    default:
        return false;
    }
}

function iterator(form, object, parentPath) {
    for (let name in object) {
        const node = object[name];
        const path = parentPath.slice();
        path.push(name);

        switch (getType(node)) {
        case 'Array':
            break;
        case 'Object':
            break;
        case 'FileList':
            break;
        case 'File':
            appendToForm(form, path, node);
            break;
        case 'Blob':
            appendToForm(form, path, node, node.name);
            break;
        default:
            appendToForm(form, path, node);
            break;
        }

        if (check(node)) {
            iterator(form, node, path);
        }
    }
}

export default function createFormData(data, prefix = []) {
    const form = new FormData;
    iterator(form, data, prefix);

    return form;
}
