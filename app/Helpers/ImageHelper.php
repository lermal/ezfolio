<?php

namespace App\Helpers;

use App\Services\ImageOptimizationService;

class ImageHelper
{
    /**
     * Generate optimized image HTML with WebP support
     *
     * @param string $originalPath
     * @param string $alt
     * @param string $class
     * @param array $attributes
     * @return string
     */
    public static function optimizedImage($originalPath, $alt = '', $class = '', $attributes = [])
    {
        $imageOptimizer = new ImageOptimizationService();
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $originalPath);
        
        $html = '<picture>';
        
        // Add WebP source if available
        if (file_exists(public_path($webpPath))) {
            $html .= '<source srcset="' . asset($webpPath) . '" type="image/webp">';
        }
        
        // Build attributes string
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        $html .= '<img src="' . asset($originalPath) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" loading="lazy"' . $attrString . '>';
        $html .= '</picture>';
        
        return $html;
    }

    /**
     * Generate responsive image with multiple sizes
     *
     * @param string $originalPath
     * @param string $alt
     * @param string $class
     * @param array $sizes
     * @return string
     */
    public static function responsiveImage($originalPath, $alt = '', $class = '', $sizes = [])
    {
        $imageOptimizer = new ImageOptimizationService();
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $originalPath);
        
        $html = '<picture>';
        
        // Add WebP sources for different sizes
        if (file_exists(public_path($webpPath))) {
            foreach ($sizes as $size) {
                $webpSizePath = str_replace(['.jpg', '.jpeg', '.png'], '_' . $size . '.webp', $originalPath);
                if (file_exists(public_path($webpSizePath))) {
                    $html .= '<source media="(max-width: ' . $size . 'px)" srcset="' . asset($webpSizePath) . '" type="image/webp">';
                }
            }
        }
        
        // Add original WebP source
        if (file_exists(public_path($webpPath))) {
            $html .= '<source srcset="' . asset($webpPath) . '" type="image/webp">';
        }
        
        // Add fallback image
        $html .= '<img src="' . asset($originalPath) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" loading="lazy">';
        $html .= '</picture>';
        
        return $html;
    }

    /**
     * Get optimized image URL
     *
     * @param string $originalPath
     * @return string
     */
    public static function getOptimizedUrl($originalPath)
    {
        $imageOptimizer = new ImageOptimizationService();
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $originalPath);
        
        if ($imageOptimizer->isWebPSupported() && file_exists(public_path($webpPath))) {
            return asset($webpPath);
        }
        
        return asset($originalPath);
    }

    /**
     * Check if WebP version exists
     *
     * @param string $originalPath
     * @return bool
     */
    public static function hasWebPVersion($originalPath)
    {
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $originalPath);
        return file_exists(public_path($webpPath));
    }
}
