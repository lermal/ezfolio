<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageOptimizationService
{
    /**
     * Optimize image and create WebP version
     *
     * @param string $imagePath
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $quality
     * @return array
     */
    public function optimizeImage($imagePath, $maxWidth = 800, $maxHeight = 600, $quality = 85)
    {
        try {
            if (!file_exists($imagePath)) {
                return [
                    'status' => false,
                    'message' => 'Image file not found'
                ];
            }

            $originalSize = filesize($imagePath);
            $pathInfo = pathinfo($imagePath);
            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';

            // Create optimized WebP version
            $image = Image::make($imagePath);
            
            // Resize if needed
            if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                $image->resize($maxWidth, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Save WebP version
            $image->encode('webp', $quality)->save($webpPath);
            
            $webpSize = filesize($webpPath);
            $savings = $originalSize - $webpSize;
            $savingsPercent = round(($savings / $originalSize) * 100, 2);

            Log::info('Image optimized successfully', [
                'original_path' => $imagePath,
                'webp_path' => $webpPath,
                'original_size' => $originalSize,
                'webp_size' => $webpSize,
                'savings' => $savings,
                'savings_percent' => $savingsPercent
            ]);

            return [
                'status' => true,
                'original_path' => $imagePath,
                'webp_path' => $webpPath,
                'original_size' => $originalSize,
                'webp_size' => $webpSize,
                'savings' => $savings,
                'savings_percent' => $savingsPercent
            ];

        } catch (\Throwable $th) {
            Log::error('Image optimization failed', [
                'image_path' => $imagePath,
                'error' => $th->getMessage()
            ]);

            return [
                'status' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    /**
     * Optimize project thumbnail
     *
     * @param string $imagePath
     * @return array
     */
    public function optimizeProjectThumbnail($imagePath)
    {
        return $this->optimizeImage($imagePath, 400, 300, 80);
    }

    /**
     * Optimize project images
     *
     * @param string $imagePath
     * @return array
     */
    public function optimizeProjectImage($imagePath)
    {
        return $this->optimizeImage($imagePath, 1200, 800, 85);
    }

    /**
     * Optimize avatar image
     *
     * @param string $imagePath
     * @return array
     */
    public function optimizeAvatar($imagePath)
    {
        return $this->optimizeImage($imagePath, 400, 400, 85);
    }

    /**
     * Get optimized image URL with WebP fallback
     *
     * @param string $originalPath
     * @param string $webpPath
     * @return string
     */
    public function getOptimizedImageUrl($originalPath, $webpPath = null)
    {
        if ($webpPath && file_exists($webpPath)) {
            return asset($webpPath);
        }
        
        return asset($originalPath);
    }

    /**
     * Check if WebP is supported by browser
     *
     * @return bool
     */
    public function isWebPSupported()
    {
        $acceptHeader = request()->header('Accept', '');
        return strpos($acceptHeader, 'image/webp') !== false;
    }

    /**
     * Generate responsive image HTML with WebP support
     *
     * @param string $originalPath
     * @param string $alt
     * @param string $class
     * @param string $webpPath
     * @return string
     */
    public function generateResponsiveImage($originalPath, $alt = '', $class = '', $webpPath = null)
    {
        $webpPath = $webpPath ?: str_replace(['.jpg', '.jpeg', '.png'], '.webp', $originalPath);
        
        $html = '<picture>';
        
        if (file_exists(public_path($webpPath))) {
            $html .= '<source srcset="' . asset($webpPath) . '" type="image/webp">';
        }
        
        $html .= '<img src="' . asset($originalPath) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" loading="lazy">';
        $html .= '</picture>';
        
        return $html;
    }
}
