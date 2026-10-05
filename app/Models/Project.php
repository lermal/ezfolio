<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslations;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, HasSlug, HasLocaleTranslations;

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
    ];
}
