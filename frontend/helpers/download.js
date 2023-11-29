export function download(filename) {
    let element = document.createElement('a');
    element.setAttribute('href', filename);
    element.setAttribute('download', filename.split('/').slice(-1));
    
    element.style.display = 'none';
    document.body.appendChild(element);
    
    element.click();
    
    document.body.removeChild(element);
}
