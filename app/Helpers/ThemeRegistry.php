<?php

namespace App\Helpers;

use Config;

class ThemeRegistry
{
    /**
     * Ids of all registered themes
     *
     * @return array
     */
    public static function ids()
    {
        return array_keys(Config::get('themes.themes', []));
    }

    /**
     * Id of the theme used when none or an unknown one is stored
     *
     * @return string
     */
    public static function defaultId()
    {
        return Config::get('themes.default');
    }

    /**
     * Check whether a theme id is registered
     *
     * @param string|null $id
     * @return bool
     */
    public static function exists($id)
    {
        return is_string($id) && in_array($id, self::ids(), true);
    }

    /**
     * Return the given id if it is registered, otherwise the default one
     *
     * @param string|null $id
     * @return string
     */
    public static function resolve($id)
    {
        return self::exists($id) ? $id : self::defaultId();
    }

    /**
     * Blade view name of a theme
     *
     * @param string $id
     * @return string
     */
    public static function view(string $id)
    {
        return 'frontend.theme.' . $id;
    }

    /**
     * Theme list for the admin panel
     *
     * @return array
     */
    public static function forAdmin()
    {
        $themes = [];

        foreach (Config::get('themes.themes', []) as $id => $theme) {
            $themes[] = [
                'id' => $id,
                'title' => $theme['title'],
                'image' => asset($theme['preview']),
            ];
        }

        return $themes;
    }
}
