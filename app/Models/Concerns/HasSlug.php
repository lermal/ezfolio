<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * A unique URL slug in the "slug" column. An empty slug is generated from the title on save.
 */
trait HasSlug
{
    public static $slugMaxLength = 120;

    /**
     * @return void
     */
    protected static function bootHasSlug()
    {
        static::saving(function ($model) {
            if (!$model->slug) {
                $model->slug = static::uniqueSlug($model->title, $model->id);
            }
        });
    }

    /**
     * URL-safe slug of the text that no other record uses
     *
     * @param string|null $text
     * @param int|null $ignoreId
     * @return string
     */
    public static function uniqueSlug($text, $ignoreId = null)
    {
        $base = static::normalizeSlug($text) ?: Str::snake(class_basename(static::class), '-');
        $slug = $base;

        for ($suffix = 2; static::slugTaken($slug, $ignoreId); $suffix++) {
            $slug = Str::limit($base, static::$slugMaxLength - strlen($suffix) - 1, '') . '-' . $suffix;
        }

        return $slug;
    }

    /**
     * @param string|null $text
     * @return string
     */
    public static function normalizeSlug($text)
    {
        return trim(Str::limit(Str::slug((string) $text), static::$slugMaxLength, ''), '-');
    }

    /**
     * @param string $slug
     * @param int|null $ignoreId
     * @return bool
     */
    public static function slugTaken(string $slug, $ignoreId = null)
    {
        return static::query()
            ->where('slug', $slug)
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->exists();
    }
}
