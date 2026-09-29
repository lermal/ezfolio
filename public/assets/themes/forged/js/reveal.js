/**
 * One-time reveal of [data-reveal] blocks when they scroll into view.
 * Stagger comes from the --i custom property set in markup.
 * Blocks already on screen at start are left as is, so nothing blinks on load.
 * Without JS, with reduced motion or without IntersectionObserver everything stays visible.
 */
export default function initReveal({ reduceMotion }) {
    const items = Array.from(document.querySelectorAll('[data-reveal]'));

    if (!items.length || reduceMotion || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-in');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px' });

    const viewportHeight = window.innerHeight;
    const onScreen = items.map((item) => {
        const rect = item.getBoundingClientRect();

        return rect.top < viewportHeight && rect.bottom > 0;
    });

    items.forEach((item, index) => {
        if (onScreen[index]) {
            item.removeAttribute('data-reveal');
        } else {
            observer.observe(item);
        }
    });

    document.documentElement.classList.add('js-reveal');
}
