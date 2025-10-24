<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Service;
use App\Models\About;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];
        
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->format('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];
        
        $projects = Project::whereNotNull('title')->get();
        foreach ($projects as $project) {
            $urls[] = [
                'loc' => url('/project/' . $project->id),
                'lastmod' => $project->updated_at->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.8'
            ];
        }
        
        $services = Service::whereNotNull('title')->get();
        foreach ($services as $service) {
            $urls[] = [
                'loc' => url('/service/' . $service->id),
                'lastmod' => $service->updated_at->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ];
        }
        
        $about = About::first();
        if ($about) {
            $urls[] = [
                'loc' => url('/about'),
                'lastmod' => $about->updated_at->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.9'
            ];
        }
        
        $urls[] = [
            'loc' => url('/portfolio'),
            'lastmod' => now()->format('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.9'
        ];
        
        $urls[] = [
            'loc' => url('/contact'),
            'lastmod' => now()->format('Y-m-d'),
            'changefreq' => 'monthly',
            'priority' => '0.8'
        ];

        $content = view('frontend.sitemap', compact('urls'));
        
        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
