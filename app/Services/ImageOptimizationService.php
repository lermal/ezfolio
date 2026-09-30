<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Responsive WebP copies of uploaded images, made with GD.
 * A copy lives next to the original as "{name}-{width}w.webp"; paths are relative to public/.
 */
class ImageOptimizationService
{
    /**
     * Widths of the WebP copies. An image narrower than a width gets one copy of its own width instead.
     */
    const WIDTHS = [480, 960, 1600];

    const QUALITY = 80;

    /**
     * Decoding a full-page screenshot takes width * height * 4 bytes
     */
    const MEMORY_LIMIT = '512M';

    /**
     * @var array path => [width => path]
     */
    private static $found = [];

    /**
     * Create the WebP copies of an image
     *
     * @param string $path
     * @param array $widths
     * @param bool $force recreate existing copies
     * @return array
     */
    public function createVariants($path, array $widths = self::WIDTHS, bool $force = false)
    {
        $full = public_path($path);

        try {
            if (!function_exists('imagewebp')) {
                return ['status' => false, 'message' => 'GD is built without WebP support'];
            }

            if (!is_file($full)) {
                return ['status' => false, 'message' => 'Image file not found'];
            }

            $this->raiseMemoryLimit();

            $source = @imagecreatefromstring(file_get_contents($full));
            if (!$source) {
                return ['status' => false, 'message' => 'Unsupported image format'];
            }

            imagepalettetotruecolor($source);
            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);

            $created = [];
            sort($widths);

            foreach ($widths as $width) {
                $width = min($width, $sourceWidth);
                $target = self::variantPath($path, $width);

                if (isset($created[$width])) {
                    continue;
                }

                if ($force || !is_file(public_path($target))) {
                    $this->saveResized($source, $sourceWidth, $sourceHeight, $width, public_path($target));
                }

                $created[$width] = $target;
            }

            imagedestroy($source);
            unset(self::$found[$path]);

            $originalSize = filesize($full);
            $largest = end($created);
            $webpSize = filesize(public_path($largest));

            return [
                'status' => true,
                'variants' => $created,
                'webp_path' => $largest,
                'original_size' => $originalSize,
                'webp_size' => $webpSize,
                'savings' => $originalSize - $webpSize,
                'savings_percent' => $originalSize ? round(($originalSize - $webpSize) / $originalSize * 100, 2) : 0,
            ];
        } catch (\Throwable $th) {
            Log::error('Image optimization failed', ['image_path' => $path, 'error' => $th->getMessage()]);

            return ['status' => false, 'message' => $th->getMessage()];
        }
    }

    /**
     * @param string $path
     * @return array
     */
    public function optimizeProjectThumbnail($path)
    {
        return $this->createVariants($path);
    }

    /**
     * @param string $path
     * @return array
     */
    public function optimizeProjectImage($path)
    {
        return $this->createVariants($path);
    }

    /**
     * @param string $path
     * @return array
     */
    public function optimizeAvatar($path)
    {
        return $this->createVariants($path, [240]);
    }

    /**
     * Existing WebP copies of an image, narrowest first
     *
     * @param string|null $path
     * @return array width => path
     */
    public static function variants($path)
    {
        if (!is_string($path) || $path === '') {
            return [];
        }

        if (!isset(self::$found[$path])) {
            $variants = [];
            $prefix = self::variantPrefix($path);

            foreach (glob(public_path($prefix) . '*w.webp') ?: [] as $file) {
                if (preg_match('/-(\d+)w\.webp$/', $file, $match)) {
                    $variants[(int) $match[1]] = $prefix . $match[1] . 'w.webp';
                }
            }

            ksort($variants);
            self::$found[$path] = $variants;
        }

        return self::$found[$path];
    }

    /**
     * Remove the WebP copies of an image
     *
     * @param string|null $path
     * @return void
     */
    public static function deleteVariants($path)
    {
        foreach (self::variants($path) as $variant) {
            @unlink(public_path($variant));
        }

        unset(self::$found[$path]);
    }

    /**
     * @param string $path
     * @param int $width
     * @return string
     */
    private static function variantPath($path, int $width)
    {
        return self::variantPrefix($path) . $width . 'w.webp';
    }

    /**
     * @param string $path
     * @return string
     */
    private static function variantPrefix($path)
    {
        $info = pathinfo($path);
        $dir = isset($info['dirname']) && $info['dirname'] !== '.' ? $info['dirname'] . '/' : '';

        return $dir . $info['filename'] . '-';
    }

    /**
     * @param resource|\GdImage $source
     * @param int $sourceWidth
     * @param int $sourceHeight
     * @param int $width
     * @param string $target
     * @return void
     */
    private function saveResized($source, int $sourceWidth, int $sourceHeight, int $width, string $target)
    {
        $height = max(1, (int) round($sourceHeight * $width / $sourceWidth));

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        imagewebp($image, $target, self::QUALITY);
        imagedestroy($image);
    }

    /**
     * @return void
     */
    private function raiseMemoryLimit()
    {
        $current = ini_get('memory_limit');

        if ($current !== '-1' && $this->bytes($current) < $this->bytes(self::MEMORY_LIMIT)) {
            @ini_set('memory_limit', self::MEMORY_LIMIT);
        }
    }

    /**
     * @param string $value
     * @return int
     */
    private function bytes($value)
    {
        $value = trim((string) $value);
        $number = (int) $value;

        switch (strtolower(substr($value, -1))) {
            case 'g':
                return $number * 1024 ** 3;
            case 'm':
                return $number * 1024 ** 2;
            case 'k':
                return $number * 1024;
        }

        return $number;
    }
}
