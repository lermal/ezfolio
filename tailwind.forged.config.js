/**
 * Tailwind config of the "forged" theme only.
 * Built by webpack.mix.js into public/assets/themes/forged/css/forged.css.
 */
module.exports = {
    content: [
        './resources/views/frontend/theme/forged.blade.php',
        './resources/views/frontend/theme/forged/**/*.blade.php',
        './public/assets/themes/forged/js/**/*.js',
    ],
    theme: {
        screens: {
            md: '768px',
            lg: '1200px',
        },
        extend: {
            colors: {
                heat: 'rgba(var(--accent-color-rgb), <alpha-value>)',
                forge: {
                    bg: '#0b0c0e',
                    surface: '#131518',
                    raised: '#1a1d21',
                    line: 'rgba(255, 255, 255, 0.08)',
                    text: '#e8e6e3',
                    muted: '#9a9ea6',
                },
            },
            fontFamily: {
                display: ['Unbounded', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                sans: ['Onest', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                mono: ['"IBM Plex Mono"', 'ui-monospace', 'SFMono-Regular', 'Consolas', 'monospace'],
            },
            borderRadius: {
                tile: '14px',
            },
            transitionTimingFunction: {
                heat: 'cubic-bezier(.2, .7, .2, 1)',
            },
        },
    },
};
