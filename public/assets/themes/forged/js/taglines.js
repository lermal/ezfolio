/**
 * Rotates hero taglines in place. The first one is rendered by the server,
 * the full list is also available to screen readers as static text.
 */
const INTERVAL = 3200;
const FADE = 250;

export default function initTaglines({ reduceMotion }) {
    const el = document.querySelector('[data-taglines]');

    if (!el || reduceMotion) {
        return;
    }

    let list;
    try {
        list = JSON.parse(el.dataset.taglines);
    } catch {
        return;
    }

    if (!Array.isArray(list) || list.length < 2) {
        return;
    }

    let index = 0;

    setInterval(() => {
        if (document.hidden) {
            return;
        }

        el.classList.add('is-swapping');

        setTimeout(() => {
            index = (index + 1) % list.length;
            el.textContent = list[index];
            el.classList.remove('is-swapping');
        }, FADE);
    }, INTERVAL);
}
