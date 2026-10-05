<?php

namespace App\Support;

class LocaleContent
{
    /**
     * @param mixed $value
     * @return array{ru: string, en: string}
     */
    public static function text($value): array
    {
        if (is_string($value) || is_numeric($value)) {
            return ['ru' => (string) $value, 'en' => ''];
        }

        if (self::isPair($value)) {
            return [
                'ru' => self::stringValue($value['ru'] ?? ''),
                'en' => self::stringValue($value['en'] ?? ''),
            ];
        }

        return ['ru' => '', 'en' => ''];
    }

    /**
     * A JSON text column that already stores {"ru","en"}, or a legacy plain string.
     *
     * @param mixed $value
     * @return array{ru: string, en: string}
     */
    public static function decodeStoredText($value): array
    {
        if (is_array($value)) {
            return self::text($value);
        }

        if (!is_string($value) || $value === '') {
            return ['ru' => '', 'en' => ''];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded) && self::isPair($decoded)) {
            return self::text($decoded);
        }

        return ['ru' => $value, 'en' => ''];
    }

    /**
     * @param mixed $value
     * @return array{ru: array, en: array}
     */
    public static function lists($value): array
    {
        $value = self::decodeJson($value);

        if (self::isPair($value)) {
            return [
                'ru' => self::stringList($value['ru'] ?? []),
                'en' => self::stringList($value['en'] ?? []),
            ];
        }

        return [
            'ru' => self::stringList($value),
            'en' => [],
        ];
    }

    /**
     * Button groups keyed by locale, or one legacy list / JSON string.
     *
     * @param mixed $value
     * @return array{ru: array, en: array}
     */
    public static function buttonGroups($value): array
    {
        $value = self::decodeJson($value);

        if (self::isPair($value)) {
            return [
                'ru' => is_array($value['ru'] ?? null) ? array_values($value['ru']) : [],
                'en' => is_array($value['en'] ?? null) ? array_values($value['en']) : [],
            ];
        }

        return [
            'ru' => is_array($value) ? array_values($value) : [],
            'en' => [],
        ];
    }

    /**
     * Drop empty English so spatie falls back to Russian.
     * An empty list is a real value and would block that fallback.
     *
     * @param array $pair
     * @return array
     */
    public static function stored(array $pair): array
    {
        $stored = [];

        foreach (['ru', 'en'] as $locale) {
            if (!array_key_exists($locale, $pair)) {
                continue;
            }

            $value = $pair[$locale];

            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $stored[$locale] = $value;
        }

        if (!array_key_exists('ru', $stored)) {
            $stored['ru'] = is_array($pair['ru'] ?? null) ? [] : '';
        }

        return $stored;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $pairs attribute => locale pair
     * @return void
     */
    public static function assign($model, array $pairs): void
    {
        foreach ($pairs as $attribute => $pair) {
            $model->replaceTranslations($attribute, self::stored($pair));
        }
    }

    /**
     * Active locale, then Russian when English is missing or blank.
     *
     * @param mixed $value
     * @return string
     */
    public static function pick($value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }

        if (!is_array($value)) {
            return '';
        }

        $locale = app()->getLocale();
        $current = $value[$locale] ?? '';

        if (is_string($current) && $current !== '') {
            return $current;
        }

        $fallback = $value['ru'] ?? '';

        return is_string($fallback) ? $fallback : '';
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isPair($value): bool
    {
        return is_array($value)
            && !self::isList($value)
            && (array_key_exists('ru', $value) || array_key_exists('en', $value));
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function decodeJson($value)
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $value;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function stringValue($value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * @param mixed $value
     * @return array
     */
    private static function stringList($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (is_string($item) && $item !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param array $value
     * @return bool
     */
    private static function isList(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }
}
