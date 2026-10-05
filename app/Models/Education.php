<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory, HasLocaleTranslations;

    /**
     * @var array
     */
    public $translatable = [
        'institution',
        'period',
        'degree',
        'department',
        'thesis',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'institution',
        'period',
        'degree',
        'cgpa',
        'department',
        'thesis'
    ];
}
