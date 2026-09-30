/**
 * Project page gallery: the server-rendered slides get the same viewer as the dialog.
 * The page element plays the dialog role for the full-screen mode and the arrow keys.
 */
import createViewer from './viewer.js';

export default function initProjectPage({ reduceMotion }) {
    const page = document.querySelector('[data-project-page]');
    const media = page && page.querySelector('[data-slot="media"]');

    if (!media) {
        return;
    }

    let sources;
    try {
        sources = JSON.parse(media.dataset.sources);
    } catch {
        return;
    }

    const viewer = createViewer({ dialog: page, root: media, thumbs: page.querySelector('[data-slot="thumbs"]'), reduceMotion });
    viewer.load(sources, page.dataset.title);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            viewer.handleCancel();
        }
    });
}
