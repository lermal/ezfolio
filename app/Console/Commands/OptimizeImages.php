<?php

namespace App\Console\Commands;

use App\Models\About;
use App\Models\Project;
use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize {--force : Recreate WebP copies that already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create responsive WebP copies of project images and the avatar';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!function_exists('imagewebp')) {
            $this->error('PHP GD is built without WebP support, install or enable it first.');

            return 1;
        }

        $optimizer = new ImageOptimizationService();
        $force = (bool) $this->option('force');
        $original = 0;
        $optimized = 0;
        $failed = 0;

        $jobs = [];

        foreach (Project::all() as $project) {
            $images = json_decode((string) $project->images, true);
            $paths = array_filter(array_merge([$project->thumbnail], is_array($images) ? $images : []));

            foreach (array_unique($paths) as $path) {
                $jobs[] = [$project->title, $path, ImageOptimizationService::WIDTHS];
            }
        }

        foreach (About::all() as $about) {
            if ($about->hasCustomAvatar()) {
                $jobs[] = [$about->name, $about->avatar, [240]];
            }
        }

        foreach ($jobs as [$owner, $path, $widths]) {
            $result = $optimizer->createVariants($path, $widths, $force);

            if (!$result['status']) {
                $failed++;
                $this->warn("✗ {$owner}: {$path} — {$result['message']}");
                continue;
            }

            $original += $result['original_size'];
            $optimized += $result['webp_size'];
            $this->line("✓ {$owner}: {$path} → " . implode(', ', array_keys($result['variants'])) . "w ({$this->formatBytes($result['original_size'])} → {$this->formatBytes($result['webp_size'])})");
        }

        $this->info('Done: ' . (count($jobs) - $failed) . ' images, ' . $failed . ' failed.');
        $this->info('Largest copies weigh ' . $this->formatBytes($optimized) . ' instead of ' . $this->formatBytes($original) . '.');

        return $failed ? 1 : 0;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
