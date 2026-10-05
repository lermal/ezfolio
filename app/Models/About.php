<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory, HasLocaleTranslations;

    /**
     * @var array
     */
    public $translatable = [
        'name',
        'address',
        'description',
        'taglines',
    ];

    /**
     * @var array
     */
    public $translatableLists = [
        'taglines',
    ];

    /**
     * Placeholder used when no photo has been uploaded
     */
    const DEFAULT_AVATAR = 'assets/common/img/avatar/default.png';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'about';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'avatar',
        'cover',
        'email',
        'phone',
        'address',
        'description',
        'taglines',
        'social_links',
        'cv',
    ];

    /**
     * A photo was uploaded, rather than the built-in placeholder
     *
     * @return bool
     */
    public function hasCustomAvatar()
    {
        return is_string($this->avatar)
            && $this->avatar !== ''
            && $this->avatar !== self::DEFAULT_AVATAR;
    }
}
