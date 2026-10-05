<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslations;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, HasSlug, HasLocaleTranslations;

    /**
     * @var array
     */
    public $translatable = [
        'title',
        'details',
        'content',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'icon',
        'details',
        'content',
    ];
}
