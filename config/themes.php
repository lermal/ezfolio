<?php

/*
|--------------------------------------------------------------------------
| Portfolio themes
|--------------------------------------------------------------------------
|
| Single registry of frontend themes. To add a theme:
|   1. add an entry below (the key is the theme id stored in the database);
|   2. create resources/views/frontend/theme/{id}.blade.php extending
|      frontend.layouts.theme;
|   3. put its assets into public/assets/themes/{id} and the preview image
|      at the "preview" path.
| The admin panel reads this list at runtime, no frontend rebuild is needed.
| A stored id that is missing here falls back to "default".
|
| "projects_widget" loads the React projects widget (antd) on the page.
| Themes that render projects themselves set it to false.
|
*/

return [
    'default' => 'custom',

    'themes' => [
        'custom' => [
            'title' => 'Custom',
            'preview' => 'assets/common/img/templates/custom.png',
            'projects_widget' => true,
        ],
        'forged' => [
            'title' => 'Forged',
            'preview' => 'assets/common/img/templates/forged.svg',
            'projects_widget' => false,
        ],
    ],
];
