/**
 * Mobile "write me" button: shown once the hero is scrolled away,
 * hidden again while the contact section is on screen.
 * Hidden on tablets and desktop by CSS.
 */
export default function initStickyCta() {
    const cta = document.querySelector('[data-sticky-cta]');
    const hero = document.querySelector('[data-hero]');

    if (!cta || !hero || !('IntersectionObserver' in window)) {
        return;
    }

    const contact = document.getElementById('contact');
    let heroVisible = true;
    let contactVisible = false;

    const update = () => {
        cta.classList.toggle('is-visible', !heroVisible && !contactVisible);
    };

    new IntersectionObserver(([entry]) => {
        heroVisible = entry.isIntersecting;
        update();
    }).observe(hero);

    if (contact) {
        new IntersectionObserver(([entry]) => {
            contactVisible = entry.isIntersecting;
            update();
        }, { threshold: 0.15 }).observe(contact);
    }
}
