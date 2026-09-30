<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    const SLUG_MAX_LENGTH = 120;

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
    ];

    /**
     * An empty slug is generated from the title
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function (Project $project) {
            if (!$project->slug) {
                $project->slug = static::uniqueSlug($project->title, $project->id);
            }
        });
    }

    /**
     * URL-safe slug of the text that no other project uses
     *
     * @param string|null $text
     * @param int|null $ignoreId
     * @return string
     */
    public static function uniqueSlug($text, $ignoreId = null)
    {
        $base = static::normalizeSlug($text) ?: 'project';
        $slug = $base;

        for ($suffix = 2; static::slugTaken($slug, $ignoreId); $suffix++) {
            $slug = Str::limit($base, self::SLUG_MAX_LENGTH - strlen($suffix) - 1, '') . '-' . $suffix;
        }

        return $slug;
    }

    /**
     * @param string|null $text
     * @return string
     */
    public static function normalizeSlug($text)
    {
        return trim(Str::limit(Str::slug((string) $text), self::SLUG_MAX_LENGTH, ''), '-');
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
