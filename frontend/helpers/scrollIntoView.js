/**
 * Метод для скрола
 * @param {HTMLElement} element элемент для показа
 * @param {Object} [options] сдвиги
 * @param {number} [options.y] сдвиг по вертикали
 * @param {'smooth' | 'auto'} [options.behavior] сдвиг по вертикали
 * @param {Window | HTMLElement} [overflowElement] контейнер для скрола
 */
export default function scrollIntoView(element, options, overflowElement = window) {
    const { y = 0, behavior = 'smooth' } = options || {};
    const top = overflowElement === window ? element.getBoundingClientRect().top : element.getBoundingClientRect().top - overflowElement.getBoundingClientRect().top;

    if (typeof element.scrollTo === 'function') {
        window.scrollTo({
            top: top + (overflowElement.pageYOffset || overflowElement.scrollTop) + y,
            behavior
        });

    } else {
        (overflowElement === window ? (document.scrollingElement || document.documentElement) : overflowElement).scrollTop = top + (overflowElement.pageYOffset || overflowElement.scrollTop) + y;
    }

    return element;
}
