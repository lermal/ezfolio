/**
 * "More" chip in the stack tile. Reveals the skills that were left out of the first row of tags.
 */
export default function initStack() {
    const root = document.querySelector('[data-stack]');
    const button = root?.querySelector('[data-stack-more]');

    if (!root || !button) {
        return;
    }

    button.addEventListener('click', () => {
        root.querySelectorAll('[data-stack-extra]').forEach((item) => {
            item.hidden = false;
        });
        button.setAttribute('aria-expanded', 'true');
        button.closest('li')?.remove();
    });
}
