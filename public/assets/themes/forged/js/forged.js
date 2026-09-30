import initTaglines from './taglines.js';
import initStickyCta from './sticky-cta.js';
import initPointerGlow from './pointer-glow.js';
import initReveal from './reveal.js';
import initFilter from './filter.js';
import initProjectDialog from './project-dialog.js';
import initProjectPage from './project-page.js';
import initStack from './stack.js';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

initReveal({ reduceMotion });
initTaglines({ reduceMotion });
initStickyCta();
initPointerGlow({ reduceMotion });
initFilter({ reduceMotion });
initProjectDialog({ reduceMotion });
initProjectPage({ reduceMotion });
initStack();
