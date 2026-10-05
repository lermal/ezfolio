<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\ThemeRegistry;
use App\Http\Controllers\Controller;
use App\Http\Middleware\DetectPreferredLocale;
use App\Models\About;
use App\Models\Project;
use App\Models\Service;
use App\Services\Contracts\PortfolioConfigInterface;
use CoreConstants;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SitemapController extends Controller
{
    /**
     * The home page dated by the latest content change, and the project and
     * service pages when the active theme has them
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

        $lastmodDate = $lastmod ? date('Y-m-d', strtotime($lastmod)) : now()->format('Y-m-d');
        $urls = $this->forLocales(url('/'), $lastmodDate);

        $config = $portfolioConfig->getAllConfigData();
        $config = $config['status'] === CoreConstants::STATUS_CODE_SUCCESS ? $config['payload'] : null;

        if ($config && !empty($config['visibility']['projects']) && ThemeRegistry::hasProjectPages($config['template'])) {
            foreach (array_keys(LaravelLocalization::getSupportedLocales()) as $locale) {
                $projects = Project::query()
                    ->visibleForLocale($locale)
                    ->whereNotNull('slug')
                    ->orderBy('id')
                    ->get(['slug', 'updated_at']);

                foreach ($projects as $project) {
                    $urls = array_merge($urls, $this->forLocales(
                        route('project', $project->slug),
                        $project->updated_at ? $project->updated_at->format('Y-m-d') : $urls[0]['lastmod'],
                        [$locale]
                    ));
                }
            }
        }

        if ($config && !empty($config['visibility']['services']) && ThemeRegistry::hasServicePages($config['template'])) {
            foreach (Service::whereNotNull('slug')->orderBy('id')->get(['slug', 'updated_at']) as $service) {
                $urls = array_merge($urls, $this->forLocales(
                    route('service', $service->slug),
                    $service->updated_at ? $service->updated_at->format('Y-m-d') : $urls[0]['lastmod']
                ));
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
            'Disallow: /en/contact-me',
            'Disallow: /locale/',
            '',
            'Sitemap: ' . route('sitemap'),
            '',
        ]);

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * One sitemap entry per public locale. The default locale stays unprefixed.
     *
     * @param  string  $url
     * @param  string  $lastmod
     * @param  array|null  $locales
     * @return array
     */
    private function forLocales($url, $lastmod, ?array $locales = null)
    {
        $entries = [];
        $locales = $locales ?? array_keys(LaravelLocalization::getSupportedLocales());

        foreach ($locales as $locale) {
            $entries[] = [
                'loc' => DetectPreferredLocale::urlFor(request(), $locale, parse_url($url, PHP_URL_PATH) ?: '/'),
                'lastmod' => $lastmod,
            ];
        }

        return $entries;
    }
}
