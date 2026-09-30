<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\ThemeRegistry;
use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\Project;
use App\Models\Service;
use App\Services\Contracts\PortfolioConfigInterface;
use CoreConstants;

class SitemapController extends Controller
{
    /**
     * The home page dated by the latest content change, and the project pages
     * when the active theme has them
     *
     * @param PortfolioConfigInterface $portfolioConfig
     * @return \Illuminate\Http\Response
     */
    public function index(PortfolioConfigInterface $portfolioConfig)
    {
        $lastmod = collect([
            About::max('updated_at'),
            Project::max('updated_at'),
            Service::max('updated_at'),
        ])->filter()->max();

        $urls = [[
            'loc' => url('/'),
            'lastmod' => $lastmod ? date('Y-m-d', strtotime($lastmod)) : now()->format('Y-m-d'),
        ]];

        $config = $portfolioConfig->getAllConfigData();
        $config = $config['status'] === CoreConstants::STATUS_CODE_SUCCESS ? $config['payload'] : null;

        if ($config && !empty($config['visibility']['projects']) && ThemeRegistry::hasProjectPages($config['template'])) {
            foreach (Project::whereNotNull('slug')->orderBy('id')->get(['slug', 'updated_at']) as $project) {
                $urls[] = [
                    'loc' => route('project', $project->slug),
                    'lastmod' => $project->updated_at ? $project->updated_at->format('Y-m-d') : $urls[0]['lastmod'],
                ];
            }
        }

        return response()
            ->view('frontend.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function robots()
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /pixel-tracker',
            'Disallow: /contact-me',
            '',
            'Sitemap: ' . route('sitemap'),
            '',
        ]);

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
