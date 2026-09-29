/**
 * Heat spot that follows the mouse inside a tile.
 * One delegated listener, at most one write per animation frame,
 * only the hovered tile is touched. Mouse only: touch and pen get no spot.
 */
export default function initPointerGlow({ reduceMotion }) {
    if (reduceMotion || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    let frame = 0;
    let last = null;

    const paint = () => {
        frame = 0;

        const tile = last.target instanceof Element ? last.target.closest('.tile') : null;
        if (!tile) {
            return;
        }

        const rect = tile.getBoundingClientRect();
        tile.style.setProperty('--mx', `${Math.round(last.clientX - rect.left)}px`);
        tile.style.setProperty('--my', `${Math.round(last.clientY - rect.top)}px`);
    };

    document.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse') {
            return;
        }

        last = event;

        if (!frame) {
            frame = requestAnimationFrame(paint);
        }
    }, { passive: true });
}
