<?php

namespace App\Helpers;

use App\Services\ImageOptimizationService;

class ImageHelper
{
    /**
     * <picture> with the responsive WebP copies and the original as the fallback.
     * The "sizes" attribute tells the browser how wide the image is shown.
     *
     * @param string $originalPath
     * @param string $alt
     * @param string $class
     * @param array $attributes
     * @return string
     */
    public static function optimizedImage($originalPath, $alt = '', $class = '', $attributes = [])
    {
        $sizes = $attributes['sizes'] ?? '100vw';
        unset($attributes['sizes']);

        $html = '<picture>';

        $variants = ImageOptimizationService::variants($originalPath);
        if ($variants) {
            $srcset = [];
            foreach ($variants as $width => $path) {
                $srcset[] = asset($path) . ' ' . $width . 'w';
            }

            $html .= '<source type="image/webp" srcset="' . e(implode(', ', $srcset)) . '" sizes="' . e($sizes) . '">';
        }

        $attributes += ['loading' => 'lazy'];
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        $html .= '<img src="' . asset($originalPath) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '"' . $attrString . '>';
        $html .= '</picture>';

        return $html;
    }

    /**
     * URL of the widest WebP copy up to $maxWidth, the original when there are no copies
     *
     * @param string $originalPath
     * @param int $maxWidth
     * @return string
     */
    public static function webpUrl($originalPath, int $maxWidth = 1600)
    {
        $fitting = array_filter(ImageOptimizationService::variants($originalPath), function ($width) use ($maxWidth) {
            return $width <= $maxWidth;
        }, ARRAY_FILTER_USE_KEY);

        return $fitting ? asset(end($fitting)) : asset($originalPath);
    }

    /**
     * srcset and sizes for a <link rel="preload" as="image">, null without WebP copies
     *
     * @param string $originalPath
     * @param string $sizes
     * @return array|null
     */
    public static function preloadAttributes($originalPath, string $sizes)
    {
        $variants = ImageOptimizationService::variants($originalPath);

        if (!$variants) {
            return null;
        }

        $srcset = [];
        foreach ($variants as $width => $path) {
            $srcset[] = asset($path) . ' ' . $width . 'w';
        }

        return ['srcset' => implode(', ', $srcset), 'sizes' => $sizes];
    }
}
