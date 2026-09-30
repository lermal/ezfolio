/**
 * One-time reveal of [data-reveal] blocks when they scroll into view.
 * Stagger comes from the --i custom property set in markup.
 * Blocks already on screen at start are left as is, so nothing blinks on load:
 * the first observer callback reports them, and only then the hiding class is added.
 * Without JS, with reduced motion or without IntersectionObserver everything stays visible.
 */
export default function initReveal({ reduceMotion }) {
    const items = Array.from(document.querySelectorAll('[data-reveal]'));

    if (!items.length || reduceMotion || !('IntersectionObserver' in window)) {
        return;
    }

    let initial = true;

    // Entry rects are computed by the observer, reading them forces no layout
    const onScreen = (entry) => entry.boundingClientRect.top < window.innerHeight && entry.boundingClientRect.bottom > 0;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (initial && onScreen(entry)) {
                observer.unobserve(entry.target);
                entry.target.removeAttribute('data-reveal');
            } else if (!initial && entry.isIntersecting) {
                observer.unobserve(entry.target);
                entry.target.classList.add('is-in');
            }
        });

        if (initial) {
            initial = false;
            document.documentElement.classList.add('js-reveal');
        }
    }, { rootMargin: '0px 0px -8% 0px' });

    items.forEach((item) => observer.observe(item));
}
