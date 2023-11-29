import { isElement } from 'lodash';

export default function isElementVisible(element) {
    if (!isElement(element)) return false;
    if (!process.browser) return false;
    const styles = window.getComputedStyle(element);

    return styles.visibility !== 'hidden' && styles.opacity !== '0' && styles.display !== 'none';
}
