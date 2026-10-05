<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory, HasLocaleTranslations;

    /**
     * @var array
     */
    public $translatable = [
        'company',
        'period',
        'position',
        'details',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company',
        'period',
        'position',
        'details'
    ];
}
