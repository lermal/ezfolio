<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageOptimizationService;
use App\Models\Project;
use App\Models\About;
use Illuminate\Support\Facades\File;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize {--force : Force optimization even if WebP already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize all existing images and create WebP versions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting image optimization...');
        
        $imageOptimizer = new ImageOptimizationService();
        $force = $this->option('force');
        
        $totalSavings = 0;
        $processedCount = 0;
        
        // Optimize project images
        $this->info('Optimizing project images...');
        $projects = Project::all();
        
        foreach ($projects as $project) {
            // Optimize thumbnail
            if ($project->thumbnail && file_exists(public_path($project->thumbnail))) {
                $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $project->thumbnail);
                
                if ($force || !file_exists(public_path($webpPath))) {
                    $result = $imageOptimizer->optimizeProjectThumbnail($project->thumbnail);
                    if ($result['status']) {
                        $totalSavings += $result['savings'];
                        $processedCount++;
                        $this->line("✓ Thumbnail optimized: {$project->title} (saved {$result['savings_percent']}%)");
                    }
                }
            }
            
            // Optimize project images
            if ($project->images) {
                $images = json_decode($project->images, true);
                if (is_array($images)) {
                    foreach ($images as $image) {
                        if (file_exists(public_path($image))) {
                            $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $image);
                            
                            if ($force || !file_exists(public_path($webpPath))) {
                                $result = $imageOptimizer->optimizeProjectImage($image);
                                if ($result['status']) {
                                    $totalSavings += $result['savings'];
                                    $processedCount++;
                                    $this->line("✓ Project image optimized: {$project->title} (saved {$result['savings_percent']}%)");
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Optimize avatar images
        $this->info('Optimizing avatar images...');
        $abouts = About::all();
        
        foreach ($abouts as $about) {
            if ($about->avatar && file_exists(public_path($about->avatar))) {
                $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $about->avatar);
                
                if ($force || !file_exists(public_path($webpPath))) {
                    $result = $imageOptimizer->optimizeAvatar($about->avatar);
                    if ($result['status']) {
                        $totalSavings += $result['savings'];
                        $processedCount++;
                        $this->line("✓ Avatar optimized: {$about->name} (saved {$result['savings_percent']}%)");
                    }
                }
            }
        }
        
        // Convert bytes to human readable format
        $totalSavingsFormatted = $this->formatBytes($totalSavings);
        
        $this->info("Image optimization completed!");
        $this->info("Processed: {$processedCount} images");
        $this->info("Total savings: {$totalSavingsFormatted}");
        
        return 0;
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
