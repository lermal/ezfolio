<?php

namespace App\Models\Concerns;

use Spatie\Translatable\HasTranslations;

/**
 * Spatie translations stored as {"ru": "...", "en": "..."}.
 * Admin JSON includes both locales; every other consumer gets the active locale.
 */
trait HasLocaleTranslations
{
    use HasTranslations;

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $forAdmin = app()->bound('request') && request()->is('api/v1/admin/*');

        foreach ($this->getTranslatableAttributes() as $attribute) {
            $array[$attribute] = $forAdmin
                ? $this->translationPair($attribute)
                : $this->getAttributeValue($attribute);
        }

        return $array;
    }

    /**
     * Both locales for the admin form. Missing English is an empty string or list,
     * so the EN tab can be filled without dropping the Russian value.
     *
     * @param string $attribute
     * @return array
     */
    public function translationPair(string $attribute): array
    {
        $translations = $this->getTranslations($attribute);
        $lists = property_exists($this, 'translatableLists') ? $this->translatableLists : [];
        $empty = in_array($attribute, $lists, true) ? [] : '';

        return [
            'ru' => array_key_exists('ru', $translations) ? $translations['ru'] : $empty,
            'en' => array_key_exists('en', $translations) ? $translations['en'] : $empty,
        ];
    }
}
