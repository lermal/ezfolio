<?php

return [

    /*
    | Supported locales. Russian is the default (config/app.php locale) because
    | the existing site copy is Russian. hideDefaultLocaleInURL keeps the
    | current URLs (/ , /projects/…, /services/…) and serves English at /en/…
    | /ru/… is redirected to the unprefixed URL by localizationRedirect.
    */

    'supportedLocales' => [
        'ru' => ['name' => 'Russian', 'script' => 'Cyrl', 'native' => 'Русский', 'regional' => 'ru_RU'],
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_US'],
    ],

    'localesOrder' => ['ru', 'en'],

    /*
    | Off on purpose. Accept-Language is applied by DetectPreferredLocale
    | only after the manual cookie/session and the Cloudflare country, so the
    | package negotiator must not run first and skip CF-IPCountry.
    */
    'useAcceptLanguageHeader' => false,

    'hideDefaultLocaleInURL' => true,

    'localesMapping' => [],

    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', '.UTF-8'),

    'urlsIgnored' => ['/admin', '/admin/*', '/locale/*', '/pixel-tracker', '/sitemap.xml', '/robots.txt'],

    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];
