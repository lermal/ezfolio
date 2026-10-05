<?php

return [

    /*
     * Empty English content falls back to Russian. This is independent of
     * app.fallback_locale, which still serves the lang files.
     */
    'fallback_locale' => 'ru',

    /*
     * Do not substitute some other locale when Russian itself is empty.
     */
    'fallback_any' => false,
];
