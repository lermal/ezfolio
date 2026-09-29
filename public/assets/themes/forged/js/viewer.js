/**
 * Image viewer of the project dialog.
 *
 * Slides are a native horizontal scroll-snap track: touch swipes and trackpad gestures
 * are handled by the browser, one gesture moves exactly one slide (scroll-snap-stop).
 * Buttons, thumbnails and the arrow keys scroll the same track, so the current index is
 * always derived from the scroll position. Full-screen mode is a CSS state of the dialog,
 * because iPhone Safari has no element Fullscreen API.
 */
export default function createViewer({ dialog, root, thumbs, reduceMotion }) {
    const track = root.querySelector('[data-slot="track"]');
    const count = root.querySelector('[data-slot="count"]');
    const prev = root.querySelector('[data-viewer="prev"]');
    const next = root.querySelector('[data-viewer="next"]');
    const zoomButton = root.querySelector('[data-viewer="zoom"]');
    const zoomIcon = zoomButton.querySelector('i');

    let total = 0;
    let current = 0;
    let settleTimer = 0;

    const clamp = (index) => Math.min(Math.max(index, 0), total - 1);

    const indexFromScroll = () => (track.clientWidth ? clamp(Math.round(track.scrollLeft / track.clientWidth)) : 0);

    function scrollToIndex(index, smooth) {
        track.scrollTo({
            left: index * track.clientWidth,
            behavior: smooth && !reduceMotion ? 'smooth' : 'auto',
        });
    }

    function revealThumb(thumb) {
        const left = thumb.offsetLeft - (thumbs.clientWidth - thumb.offsetWidth) / 2;

        thumbs.scrollTo({ left, behavior: reduceMotion ? 'auto' : 'smooth' });
    }

    function render(index) {
        current = index;
        count.textContent = `${index + 1} / ${total}`;
        prev.disabled = index === 0;
        next.disabled = index === total - 1;

        Array.from(thumbs.children).forEach((thumb, thumbIndex) => {
            const active = thumbIndex === index;

            if (active) {
                thumb.setAttribute('aria-current', 'true');
                revealThumb(thumb);
            } else {
                thumb.removeAttribute('aria-current');
            }
        });
    }

    function go(index) {
        if (total > 1) {
            scrollToIndex(clamp(index), true);
        }
    }

    function setZoom(on) {
        dialog.classList.toggle('is-zoomed', on);
        zoomButton.setAttribute('aria-label', on ? zoomButton.dataset.labelUnzoom : zoomButton.dataset.labelZoom);
        zoomIcon.className = on ? 'fas fa-compress' : 'fas fa-expand';
        // The track width changes with the mode, keep the same slide in view
        requestAnimationFrame(() => scrollToIndex(current, false));
    }

    const isZoomed = () => dialog.classList.contains('is-zoomed');

    function slideFor(src, alt, index) {
        const image = new Image();
        image.src = src;
        image.alt = alt;
        image.decoding = 'async';
        image.draggable = false;
        image.loading = index === 0 ? 'eager' : 'lazy';

        const slide = document.createElement('figure');
        slide.className = 'viewer__slide';
        slide.setAttribute('role', 'group');
        slide.setAttribute('aria-roledescription', 'slide');
        slide.setAttribute('aria-label', `${index + 1} / ${total}`);
        slide.append(image);

        return slide;
    }

    function thumbFor(src, alt, index) {
        const image = new Image();
        image.src = src;
        image.alt = '';
        image.loading = 'lazy';
        image.decoding = 'async';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'viewer-thumbs__item';
        button.setAttribute('aria-label', alt);
        button.append(image);
        button.addEventListener('click', () => go(index));

        return button;
    }

    function load(sources, title) {
        total = sources.length;
        current = 0;

        const alt = (index) => (total > 1 ? `${title} — ${index + 1}` : title);

        track.replaceChildren(...sources.map((src, index) => slideFor(src, alt(index), index)));
        thumbs.replaceChildren(...(total > 1 ? sources.map((src, index) => thumbFor(src, alt(index), index)) : []));

        root.hidden = total === 0;
        root.classList.toggle('is-single', total < 2);
        thumbs.hidden = total < 2;
        track.scrollLeft = 0;

        if (total > 0) {
            render(0);
        }
    }

    function reset() {
        if (isZoomed()) {
            setZoom(false);
        }
    }

    track.addEventListener('scroll', () => {
        clearTimeout(settleTimer);
        settleTimer = setTimeout(() => {
            const index = indexFromScroll();

            if (index !== current) {
                render(index);
            }
        }, 60);
    }, { passive: true });

    track.addEventListener('click', (event) => {
        if (event.target.closest('.viewer__slide img')) {
            setZoom(!isZoomed());
        }
    });

    prev.addEventListener('click', () => go(current - 1));
    next.addEventListener('click', () => go(current + 1));
    zoomButton.addEventListener('click', () => setZoom(!isZoomed()));

    dialog.addEventListener('keydown', (event) => {
        if (total < 2 || event.altKey || event.ctrlKey || event.metaKey || event.target.closest('input, textarea, select')) {
            return;
        }

        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            go(current + (event.key === 'ArrowRight' ? 1 : -1));
        } else if (event.key === 'Home' || event.key === 'End') {
            event.preventDefault();
            go(event.key === 'Home' ? 0 : total - 1);
        }
    });

    return {
        load,
        reset,
        // Escape leaves full-screen mode first, a second press closes the dialog
        handleCancel() {
            if (isZoomed()) {
                setZoom(false);
                return true;
            }

            return false;
        },
    };
}
