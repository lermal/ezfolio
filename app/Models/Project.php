<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslations;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, HasSlug, HasLocaleTranslations;

    /**
     * Public audiences. ru is the unprefixed site, en is /en/.
     */
    public const AUDIENCES = ['ru', 'en'];

    /**
     * @var array
     */
    public $translatable = [
        'title',
        'categories',
        'details',
        'buttons',
    ];

    /**
     * @var array
     */
    public $translatableLists = [
        'categories',
        'buttons',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'categories',
        'thumbnail',
        'images',
        'details',
        'link',
        'is_featured',
        'buttons',
        'visible_locales',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'visible_locales' => 'array',
    ];

    /**
     * Existing and new projects stay visible to both audiences until one is unchecked.
     *
     * @var array
     */
    protected $attributes = [
        'visible_locales' => '["ru","en"]',
    ];

    /**
     * Locales the project may be shown to. Null or an empty set means both.
     *
     * @param mixed $value
     * @return array
     */
    public static function normalizeVisibleLocales($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        $selected = [];

        foreach ($value as $locale) {
            if (!is_string($locale)) {
                continue;
            }

            $locale = strtolower(trim($locale));

            if (in_array($locale, self::AUDIENCES, true)) {
                $selected[$locale] = true;
            }
        }

        return array_values(array_filter(self::AUDIENCES, function ($locale) use ($selected) {
            return isset($selected[$locale]);
        }));
    }

    /**
     * Audiences that may open this project. Null and an empty set mean both.
     *
     * @return array
     */
    public function audiences(): array
    {
        $locales = static::normalizeVisibleLocales($this->visible_locales);

        return $locales === [] ? self::AUDIENCES : $locales;
    }

    /**
     * A supported audience code, or the active locale when the given one is not.
     *
     * @param mixed $locale
     * @return string
     */
    public static function audienceLocale($locale = null): string
    {
        if (is_string($locale) && in_array($locale, self::AUDIENCES, true)) {
            return $locale;
        }

        $current = app()->getLocale();

        return in_array($current, self::AUDIENCES, true) ? $current : self::AUDIENCES[0];
    }

    /**
     * Projects that may be listed and opened for this audience.
     * Null and an empty set stay visible everywhere.
     *
     * @param Builder $query
     * @param string|null $locale
     * @return Builder
     */
    public function scopeVisibleForLocale(Builder $query, ?string $locale = null): Builder
    {
        $locale = static::audienceLocale($locale);

        return $query->where(function (Builder $query) use ($locale) {
            $query->whereNull('visible_locales')
                ->orWhereJsonLength('visible_locales', 0)
                ->orWhereJsonContains('visible_locales', $locale);
        });
    }
}
