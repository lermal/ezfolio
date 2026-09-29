/**
 * Category filter of the project grid. Cards move to their new places
 * with a View Transition: each visible card gets a unique name for the
 * duration of the transition only.
 */
export default function initFilter({ reduceMotion }) {
    const group = document.querySelector('[data-filter-group]');
    const grid = document.querySelector('[data-works]');

    if (!group || !grid) {
        return;
    }

    const cards = Array.from(grid.querySelectorAll('[data-categories]')).map((card) => {
        let categories = [];
        try {
            categories = JSON.parse(card.dataset.categories);
        } catch {
            categories = [];
        }

        return { card, categories };
    });
    const buttons = Array.from(group.querySelectorAll('[data-filter]'));
    const canMorph = typeof document.startViewTransition === 'function' && !reduceMotion;

    const apply = (value) => {
        cards.forEach(({ card, categories }) => {
            card.hidden = value !== '' && !categories.includes(value);

            // Cards shown by the filter may not have been scrolled into view yet
            if (!card.hidden) {
                card.classList.add('is-in');
            }
        });
        buttons.forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.filter === value));
        });
    };

    group.addEventListener('click', (event) => {
        const button = event.target.closest('[data-filter]');

        if (!button || button.getAttribute('aria-pressed') === 'true') {
            return;
        }

        if (!canMorph) {
            apply(button.dataset.filter);
            return;
        }

        cards.forEach(({ card }, index) => {
            card.style.viewTransitionName = `forged-work-${index}`;
        });

        document.startViewTransition(() => apply(button.dataset.filter)).finished
            .catch(() => {})
            .then(() => {
                cards.forEach(({ card }) => {
                    card.style.viewTransitionName = '';
                });
            });
    });
}
