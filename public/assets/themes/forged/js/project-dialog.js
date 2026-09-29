/**
 * Project dialog.
 *
 * Opening morphs the clicked card into the dialog with the View Transitions API:
 * the card preview and title get view-transition-name right before the transition,
 * the dialog cover and title get the same names in the new state. Names are set
 * on one element at a time because they must be unique on the page.
 *
 * Every open project has its own URL (?project=ID): it can be shared, a page loaded
 * with it opens the dialog, and the browser "back" button closes it.
 * Without View Transitions support or with reduced motion the dialog opens with a plain fade.
 */
const MEDIA = 'forged-media';
const TITLE = 'forged-title';

export default function initProjectDialog({ reduceMotion }) {
    const dialog = document.getElementById('project-dialog');
    const island = document.getElementById('forged-projects');

    if (!dialog || !island) {
        return;
    }

    let projects;
    try {
        projects = JSON.parse(island.textContent);
    } catch {
        return;
    }

    const byId = new Map(projects.map((project) => [String(project.id), project]));
    const slot = (name) => dialog.querySelector(`[data-slot="${name}"]`);
    const ui = {
        sheet: dialog.querySelector('.project-dialog__sheet'),
        media: slot('media'),
        cover: slot('cover'),
        title: slot('title'),
        categories: slot('categories'),
        details: slot('details'),
        galleryWrap: slot('gallery-wrap'),
        gallery: slot('gallery'),
        link: slot('link'),
    };

    const canMorph = typeof document.startViewTransition === 'function' && !reduceMotion;
    dialog.classList.toggle('is-plain', !canMorph);

    let trigger = null;
    let pushed = false;

    const setNames = (media, title, on) => {
        if (media) {
            media.style.viewTransitionName = on ? MEDIA : '';
        }
        if (title) {
            title.style.viewTransitionName = on ? TITLE : '';
        }
    };

    const tagCard = (card, on) => {
        if (card) {
            setNames(card.querySelector('[data-vt-media]'), card.querySelector('[data-vt-title]'), on);
        }
    };

    const tagDialog = (on) => setNames(ui.media, ui.title, on);

    const isOnScreen = (element) => {
        const rect = element.getBoundingClientRect();

        return rect.width > 0 && rect.bottom > 0 && rect.top < window.innerHeight;
    };

    const morph = (update) => document.startViewTransition(update).finished.catch(() => {});

    const findCard = (id) => Array.from(document.querySelectorAll(`[data-project-open="${CSS.escape(String(id))}"]`))
        .find((element) => element.offsetParent !== null) || null;

    const urlFor = (id) => {
        const url = new URL(window.location.href);

        if (id === null) {
            url.searchParams.delete('project');
        } else {
            url.searchParams.set('project', id);
        }

        return url;
    };

    function fill(project) {
        if (project.cover) {
            ui.cover.src = project.cover;
        } else {
            ui.cover.removeAttribute('src');
        }
        ui.cover.alt = project.title;
        ui.media.hidden = !project.cover;

        ui.title.textContent = project.title;
        ui.categories.textContent = project.categories.join(' · ');
        ui.categories.hidden = project.categories.length === 0;
        ui.details.textContent = project.details || '';
        ui.details.hidden = !project.details;

        ui.gallery.replaceChildren(...project.images.map((src, index) => {
            const image = new Image();
            image.src = src;
            image.alt = `${project.title} — ${index + 1}`;
            image.loading = 'lazy';
            image.decoding = 'async';

            return image;
        }));
        ui.galleryWrap.hidden = project.images.length === 0;

        if (project.link) {
            ui.link.href = project.link;
        } else {
            ui.link.removeAttribute('href');
        }
        ui.link.hidden = !project.link;

        ui.sheet.scrollTop = 0;
    }

    function open(id, card, { push }) {
        const project = byId.get(String(id));

        if (!project || dialog.open) {
            return false;
        }

        trigger = card;

        if (canMorph && card && isOnScreen(card)) {
            tagCard(card, true);
            morph(() => {
                tagCard(card, false);
                fill(project);
                dialog.showModal();
                tagDialog(true);
            }).then(() => tagDialog(false));
        } else {
            fill(project);
            dialog.showModal();
        }

        if (push) {
            window.history.pushState({ forgedProject: String(id) }, '', urlFor(String(id)));
        }
        pushed = push;

        return true;
    }

    function hide() {
        if (!dialog.open) {
            return;
        }

        const card = trigger;

        if (canMorph && card && isOnScreen(card)) {
            tagDialog(true);
            morph(() => {
                tagDialog(false);
                dialog.close();
                tagCard(card, true);
            }).then(() => tagCard(card, false));
        } else {
            dialog.close();
        }

        pushed = false;
    }

    // User intent to close: an entry we pushed is left with "back", popstate does the rest
    function close() {
        if (pushed) {
            window.history.back();
        } else {
            window.history.replaceState(window.history.state, '', urlFor(null));
            hide();
        }
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-project-open]');

        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        if (open(link.dataset.projectOpen, link, { push: true })) {
            event.preventDefault();
        }
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog || event.target.closest('[data-close]')) {
            close();
        }
    });

    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        close();
    });

    dialog.addEventListener('close', () => {
        if (trigger) {
            trigger.focus({ preventScroll: true });
        }
    });

    window.addEventListener('popstate', () => {
        const id = new URL(window.location.href).searchParams.get('project');

        if (id && !dialog.open) {
            open(id, findCard(id), { push: false });
            pushed = Boolean(window.history.state && window.history.state.forgedProject);
        } else if (!id && dialog.open) {
            hide();
        }
    });

    const initial = new URL(window.location.href).searchParams.get('project');
    if (initial) {
        open(initial, null, { push: false });
    }
}
