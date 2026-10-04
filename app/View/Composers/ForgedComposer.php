<?php

namespace App\View\Composers;

use App\Helpers\ImageHelper;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Prepares data for the "forged" theme: which bento tiles and sections are shown,
 * their spans and numbering, the project cards and the data of the project dialog.
 */
class ForgedComposer
{
    /**
     * Skills shown in the stack tile before the "more" chip
     */
    const STACK_LIMIT = 8;

    /**
     * Every n-th project card is wide, starting with the first one
     */
    const WIDE_CARD_EVERY = 5;

    /**
     * Project cards under a project page
     */
    const OTHER_WORKS = 3;

    /**
     * Rendered widths of the images for the "sizes" attribute, matching the grid in forged.css
     */
    const SIZES_CARD = '(min-width: 1200px) 440px, (min-width: 768px) 50vw, 100vw';
    const SIZES_WIDE = '(min-width: 1200px) 900px, 100vw';
    const SIZES_FEATURED = '(min-width: 1200px) 560px, 100vw';

    /**
     * Span of the small tiles in the 6-column tablet grid, by their count
     */
    const TABLET_SPANS = [
        1 => [6],
        2 => [3, 3],
        3 => [3, 3, 6],
    ];

    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData();
        $config = $data['portfolioConfig'];
        $visibility = $config['visibility'];
        $about = $data['about'];

        $skills = $this->visibleList($data, $visibility, 'skills');
        $experiences = $this->visibleList($data, $visibility, 'experiences');
        $education = $this->visibleList($data, $visibility, 'education');
        $services = $this->visibleList($data, $visibility, 'services');
        $works = $this->works($this->visibleList($data, $visibility, 'projects'));
        $featured = $works->firstWhere('featured', true) ?? $works->first();
        $socials = $this->decodeList($about->social_links);
        $current = isset($data['project']) ? $works->firstWhere('id', $data['project']->id) : null;
        $currentService = isset($data['service']) ? $services->firstWhere('id', $data['service']->id) : null;
        // Anchors of the home page sections are prefixed with it on other pages
        $home = $current || $currentService ? url('/') : '';
        $cta = $this->cta($about, $visibility, $home);

        $hasContacts = $cta !== null || !empty($about->phone) || !empty($socials);

        $tileAvailability = [
            'featured' => $featured !== null,
            'about' => $this->isVisible($visibility, 'about'),
            'stack' => $skills->isNotEmpty(),
            'contact' => $hasContacts,
        ];

        $tiles = $this->tiles($tileAvailability);

        $view->with('forged', [
            'taglines' => array_values(array_filter($this->decodeList($about->taglines), 'is_string')),
            'heatInk' => $this->inkFor($config['accentColor']),
            'cta' => $cta,
            'cv' => $this->isVisible($visibility, 'cv') && !empty($about->cv) ? $about->cv : null,
            'featured' => $featured,
            'stats' => $this->stats($data, $visibility),
            'stack' => [
                'shown' => $skills->take(self::STACK_LIMIT),
                'hidden' => $skills->slice(self::STACK_LIMIT)->values(),
            ],
            'socials' => $socials,
            'tiles' => $tiles,
            'pair' => $tileAvailability['featured'] && $tileAvailability['about'],
            'works' => $works,
            'categories' => $works->pluck('categories')->flatten()->filter()->unique()->values(),
            'dialog' => $this->dialogData($works),
            'resume' => [
                'experiences' => $experiences,
                'education' => $education,
                'skills' => $skills,
                'proficiency' => $this->isVisible($visibility, 'skillProficiency'),
            ],
            'services' => $services,
            'sections' => $this->sections([
                'projects' => $works->isNotEmpty(),
                'resume' => $experiences->isNotEmpty() || $education->isNotEmpty() || $skills->isNotEmpty(),
                'services' => $services->isNotEmpty(),
                'contact' => $this->isVisible($visibility, 'contact'),
            ], count($tiles)),
            'footer' => $this->isVisible($visibility, 'footer'),
            'assets' => $this->assets(),
            'home' => $home,
            'lcpImage' => $home === '' && $featured && $featured['thumbnail']
                ? ImageHelper::preloadAttributes($featured['thumbnail'], self::SIZES_FEATURED)
                : null,
            'page' => $current ? [
                'project' => $current,
                'images' => array_values(array_unique(array_map([ImageHelper::class, 'webpUrl'], array_filter(array_merge([$current['thumbnail']], $current['images']))))),
                'link' => $current['link'] && preg_match('#^https?://#i', $current['link']) ? $current['link'] : null,
                'buttons' => $this->projectButtons($current['buttons']),
                'others' => $this->otherWorks($works, $current),
            ] : null,
            'servicePage' => $currentService ? [
                'service' => $currentService,
                'blocks' => $this->contentBlocks($currentService->content),
                'works' => $this->worksForService($works, $currentService),
                'others' => $services->reject(function ($service) use ($currentService) {
                    return $service->id === $currentService->id;
                })->values(),
            ] : null,
            'schema' => $current
                ? $this->projectSchema($about, $current)
                : ($currentService
                    ? $this->serviceSchema($about, $currentService)
                    : $this->schema($about, $socials, $skills, $education, $services, $works)),
        ]);
    }

    /**
     * Example projects for a service page: projects with a category named in the
     * service title first ("SEO" for "SEO-оптимизация"), then the featured and the rest
     *
     * @param Collection $works
     * @param object $service
     * @return Collection
     */
    private function worksForService(Collection $works, $service)
    {
        $matches = function ($work) use ($service) {
            foreach ($work['categories'] as $category) {
                if (mb_strlen($category) >= 3 && mb_stripos((string) $service->title, $category) !== false) {
                    return true;
                }
            }

            return false;
        };

        return $works->filter($matches)
            ->merge($works->reject($matches)->sortByDesc('featured'))
            ->take(self::OTHER_WORKS)
            ->values()
            ->map(function ($work, $position) {
                $work['style'] = '--span-lg: 4; --span-md: 3; --i: ' . $position . ';';
                $work['sizes'] = self::SIZES_CARD;

                return $work;
            });
    }

    /**
     * Blocks of a plain-text page body: "## " starts a subheading, "- " a list item,
     * an empty line ends a paragraph or a list
     *
     * @param string|null $text
     * @return array [['type' => 'h2'|'p'|'ul', 'text' => string, 'items' => array]]
     */
    private function contentBlocks($text)
    {
        $blocks = [];
        $open = null;

        foreach (preg_split('/\R/u', trim((string) $text)) as $line) {
            $line = trim($line);

            if ($line === '') {
                $open = null;
            } elseif (strpos($line, '## ') === 0) {
                $blocks[] = ['type' => 'h2', 'text' => trim(substr($line, 3))];
                $open = null;
            } elseif (preg_match('/^[-•*]\s+(.+)$/u', $line, $match)) {
                if ($open === null || $blocks[$open]['type'] !== 'ul') {
                    $blocks[] = ['type' => 'ul', 'items' => []];
                    $open = count($blocks) - 1;
                }
                $blocks[$open]['items'][] = $match[1];
            } else {
                if ($open === null || $blocks[$open]['type'] !== 'p') {
                    $blocks[] = ['type' => 'p', 'text' => $line];
                    $open = count($blocks) - 1;
                } else {
                    $blocks[$open]['text'] .= "\n" . $line;
                }
            }
        }

        return $blocks;
    }

    /**
     * schema.org graph of a service page
     *
     * @param object $about
     * @param object $service
     * @return array
     */
    private function serviceSchema($about, $service)
    {
        $home = url('/');
        $url = route('service', $service->slug);

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebPage',
                    '@id' => $url . '#page',
                    'url' => $url,
                    'name' => $service->title,
                    'inLanguage' => str_replace('_', '-', app()->getLocale()),
                    'isPartOf' => ['@id' => $home . '#website'],
                    'mainEntity' => ['@id' => $url . '#service'],
                    'breadcrumb' => ['@id' => $url . '#breadcrumb'],
                ],
                array_filter([
                    '@type' => 'Service',
                    '@id' => $url . '#service',
                    'name' => $service->title,
                    'serviceType' => $service->title,
                    'url' => $url,
                    'description' => $this->plainText($service->details . ' ' . $service->content) ?: null,
                    'areaServed' => $about->address ?: null,
                    'provider' => ['@type' => 'Person', '@id' => $home . '#person', 'name' => $about->name, 'url' => $home],
                ]),
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $url . '#breadcrumb',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => $about->name, 'item' => $home],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => $service->title],
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $home . '#website',
                    'url' => $home,
                    'name' => $about->name,
                ],
            ],
        ];
    }

    /**
     * Projects suggested under a project page: the ones after it, wrapping around
     *
     * @param Collection $works
     * @param array $current
     * @return Collection
     */
    private function otherWorks(Collection $works, array $current)
    {
        $index = $works->search(function ($work) use ($current) {
            return $work['id'] === $current['id'];
        });

        return $works->slice($index + 1)
            ->merge($works->take($index))
            ->take(self::OTHER_WORKS)
            ->values()
            ->map(function ($work, $position) {
                $work['style'] = '--span-lg: 4; --span-md: 3; --i: ' . $position . ';';
                $work['sizes'] = self::SIZES_CARD;

                return $work;
            });
    }

    /**
     * Plain one-line text for structured data
     *
     * @param mixed $value
     * @return string
     */
    private function plainText($value)
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)));
    }

    /**
     * schema.org graph of a project page
     *
     * @param object $about
     * @param array $work
     * @return array
     */
    private function projectSchema($about, array $work)
    {
        $home = url('/');
        $images = array_values(array_unique(array_map('asset', array_filter(array_merge([$work['thumbnail']], $work['images'])))));

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebPage',
                    '@id' => $work['url'] . '#page',
                    'url' => $work['url'],
                    'name' => $work['title'],
                    'inLanguage' => str_replace('_', '-', app()->getLocale()),
                    'isPartOf' => ['@id' => $home . '#website'],
                    'mainEntity' => ['@id' => $work['url'] . '#work'],
                    'breadcrumb' => ['@id' => $work['url'] . '#breadcrumb'],
                ],
                array_filter([
                    '@type' => 'CreativeWork',
                    '@id' => $work['url'] . '#work',
                    'name' => $work['title'],
                    'url' => $work['url'],
                    'image' => $images ?: null,
                    'description' => $this->plainText($work['details']) ?: null,
                    'keywords' => $work['categories'] ? implode(', ', $work['categories']) : null,
                    'sameAs' => $work['link'] && preg_match('#^https?://#i', $work['link']) ? $work['link'] : null,
                    'creator' => ['@type' => 'Person', '@id' => $home . '#person', 'name' => $about->name, 'url' => $home],
                ]),
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $work['url'] . '#breadcrumb',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => $about->name, 'item' => $home],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => $work['title']],
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $home . '#website',
                    'url' => $home,
                    'name' => $about->name,
                ],
            ],
        ];
    }

    /**
     * schema.org graph of the page: the profile, its person and their works
     *
     * @param object $about
     * @param array $socials
     * @param Collection $skills
     * @param Collection $education
     * @param Collection $services
     * @param Collection $works
     * @return array
     */
    private function schema($about, array $socials, Collection $skills, Collection $education, Collection $services, Collection $works)
    {
        $url = url('/');
        $personId = $url . '#person';
        $text = function ($value) {
            return $this->plainText($value);
        };

        $person = array_filter([
            '@type' => 'Person',
            '@id' => $personId,
            'name' => $about->name,
            'url' => $url,
            'image' => $about->hasCustomAvatar() ? asset($about->avatar) : null,
            'description' => $text($about->description) ?: null,
            'email' => $about->email ? 'mailto:' . $about->email : null,
            'telephone' => $about->phone ?: null,
            'address' => $about->address ?: null,
            'sameAs' => array_values(array_filter(array_map(function ($social) {
                $link = is_array($social) ? ($social['link'] ?? '') : '';

                return preg_match('#^https?://#i', $link) ? $link : null;
            }, $socials))) ?: null,
            'knowsAbout' => $skills->pluck('name')->filter()->values()->all() ?: null,
            'alumniOf' => $education->pluck('institution')->filter()->map(function ($institution) {
                return ['@type' => 'EducationalOrganization', 'name' => $institution];
            })->values()->all() ?: null,
            'makesOffer' => $services->filter(function ($service) {
                return !empty($service->title);
            })->map(function ($service) use ($text, $personId) {
                return [
                    '@type' => 'Offer',
                    'itemOffered' => array_filter([
                        '@type' => 'Service',
                        'name' => $service->title,
                        'url' => $service->slug ? route('service', $service->slug) : null,
                        'description' => $text($service->details) ?: null,
                        'provider' => ['@id' => $personId],
                    ]),
                ];
            })->values()->all() ?: null,
        ]);

        $graph = [
            [
                '@type' => 'WebSite',
                '@id' => $url . '#website',
                'url' => $url,
                'name' => $about->name,
                'inLanguage' => str_replace('_', '-', app()->getLocale()),
                'publisher' => ['@id' => $personId],
            ],
            [
                '@type' => 'ProfilePage',
                '@id' => $url . '#profile',
                'url' => $url,
                'isPartOf' => ['@id' => $url . '#website'],
                'mainEntity' => ['@id' => $personId],
            ],
            $person,
        ];

        if ($works->isNotEmpty()) {
            $graph[] = [
                '@type' => 'ItemList',
                '@id' => $url . '#projects',
                'itemListElement' => $works->values()->map(function ($work, $index) use ($text, $personId) {
                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'item' => array_filter([
                            '@type' => 'CreativeWork',
                            'name' => $work['title'],
                            'image' => $work['thumbnail'] ? asset($work['thumbnail']) : null,
                            'description' => $text($work['details']) ?: null,
                            'url' => $work['url'],
                            'sameAs' => $work['link'] && preg_match('#^https?://#i', $work['link']) ? $work['link'] : null,
                            'keywords' => $work['categories'] ? implode(', ', $work['categories']) : null,
                            'creator' => ['@id' => $personId],
                        ]),
                    ];
                })->all(),
            ];
        }

        return ['@context' => 'https://schema.org', '@graph' => $graph];
    }

    /**
     * Theme asset URLs versioned by file modification time.
     * Nested ES module imports can't carry a query string, so every module
     * gets a versioned URL through an import map.
     *
     * @return array
     */
    private function assets()
    {
        $versioned = function ($path) {
            $file = public_path($path);

            return asset($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
        };

        $modules = [];
        foreach (glob(public_path('assets/themes/forged/js/*.js')) ?: [] as $file) {
            $path = 'assets/themes/forged/js/' . basename($file);
            $modules[asset($path)] = $versioned($path);
        }

        $css = public_path('assets/themes/forged/css/forged.css');

        return [
            'css' => $versioned('assets/themes/forged/css/forged.css'),
            // Printed into <head>: saves the render-blocking request, the stylesheet is ~10 KB gzipped
            'inlineCss' => is_file($css) ? file_get_contents($css) : null,
            'entry' => $versioned('assets/themes/forged/js/forged.js'),
            'importMap' => ['imports' => (object) $modules],
        ];
    }

    /**
     * Sections below the first screen, numbered after the tiles
     *
     * @param array $available section id => whether it has content
     * @param int $offset
     * @return array
     */
    private function sections(array $available, int $offset)
    {
        $sections = [];

        foreach (array_keys(array_filter($available)) as $index => $id) {
            $sections[] = ['id' => $id, 'number' => sprintf('%02d', $offset + $index + 1)];
        }

        return $sections;
    }

    /**
     * Project cards with decoded JSON columns and grid spans
     *
     * @param Collection $projects
     * @return Collection
     */
    private function works(Collection $projects)
    {
        $useWide = $projects->count() >= 3;

        return $projects->values()->map(function ($project, $index) use ($useWide) {
            $wide = $useWide && $index % self::WIDE_CARD_EVERY === 0;

            return [
                'id' => $project->id,
                'title' => $project->title,
                'url' => route('project', $project->slug),
                'thumbnail' => $project->thumbnail,
                'categories' => array_values(array_filter($this->decodeList($project->categories), 'is_string')),
                'images' => array_values(array_filter($this->decodeList($project->images), 'is_string')),
                'details' => $project->details,
                'link' => $project->link,
                'buttons' => $project->buttons,
                'featured' => (bool) $project->is_featured,
                'style' => ($wide ? '--span-lg: 8; --span-md: 6;' : '--span-lg: 4; --span-md: 3;') . ' --i: ' . ($index % 3) . ';',
                'sizes' => $wide ? self::SIZES_WIDE : self::SIZES_CARD,
            ];
        });
    }

    /**
     * Data for the project dialog, printed as a JSON island
     *
     * @param Collection $works
     * @return array
     */
    private function dialogData(Collection $works)
    {
        return $works->map(function ($work) {
            return [
                'id' => $work['id'],
                'title' => $work['title'],
                'url' => $work['url'],
                'categories' => $work['categories'],
                'cover' => $work['thumbnail'] ? ImageHelper::webpUrl($work['thumbnail']) : null,
                'images' => array_map([ImageHelper::class, 'webpUrl'], $work['images']),
                'details' => $work['details'],
                'link' => $work['link'] && preg_match('#^https?://#i', $work['link']) ? $work['link'] : null,
                'buttons' => $this->projectButtons($work['buttons']),
            ];
        })->values()->all();
    }

    /**
     * A list from the page data, empty when its section is hidden in the admin panel
     *
     * @param array $data
     * @param array $visibility
     * @param string $key
     * @return Collection
     */
    private function visibleList(array $data, array $visibility, string $key)
    {
        return $this->isVisible($visibility, $key) ? collect($data[$key] ?? []) : collect();
    }

    /**
     * Ordered first-screen tiles with their grid spans.
     * With both a featured project and an about tile, about sits under the hero
     * and the project covers those two rows.
     *
     * @param array $available tile id => whether it has content
     * @return array
     */
    private function tiles(array $available)
    {
        $pair = $available['featured'] && $available['about'];

        $tiles = [
            ['id' => 'hero', 'lg' => $available['featured'] ? 7 : 12, 'md' => 6, 'rows' => 1],
        ];

        if ($available['featured']) {
            $tiles[] = ['id' => 'featured', 'lg' => 5, 'md' => 6, 'rows' => $pair ? 2 : 1];
        }

        $small = array_keys(array_filter([
            'about' => $available['about'],
            'stack' => $available['stack'],
            'contact' => $available['contact'],
        ]));

        $bottomCount = count($small) - ($pair ? 1 : 0);

        foreach ($small as $index => $id) {
            $tiles[] = [
                'id' => $id,
                'lg' => ($pair && $id === 'about') ? 7 : intdiv(12, $bottomCount),
                'md' => self::TABLET_SPANS[count($small)][$index],
                'rows' => 1,
            ];
        }

        foreach ($tiles as $index => &$tile) {
            $tile['number'] = sprintf('%02d', $index + 1);
            $tile['style'] = "--span-lg: {$tile['lg']}; --span-md: {$tile['md']}; --rows: {$tile['rows']};";
        }

        return $tiles;
    }

    /**
     * Numbers for the about tile, zero values are dropped
     *
     * @param array $data
     * @param array $visibility
     * @return array
     */
    private function stats(array $data, array $visibility)
    {
        $stats = [];

        if ($this->isVisible($visibility, 'experiences')) {
            $stats['years'] = $this->yearsOfExperience(collect($data['experiences'] ?? []));
        }

        if ($this->isVisible($visibility, 'projects')) {
            $stats['projects'] = collect($data['projects'] ?? [])->count();
        }

        if ($this->isVisible($visibility, 'services')) {
            $stats['services'] = collect($data['services'] ?? [])->count();
        }

        return array_filter($stats);
    }

    /**
     * Years since the earliest year mentioned in experience periods ("2017-2019", "2019-Present")
     *
     * @param Collection $experiences
     * @return int
     */
    private function yearsOfExperience(Collection $experiences)
    {
        $years = [];

        foreach ($experiences as $experience) {
            preg_match_all('/\b(?:19|20)\d{2}\b/', (string) $experience->period, $matches);
            $years = array_merge($years, array_map('intval', $matches[0]));
        }

        return $years ? max(0, (int) date('Y') - min($years)) : 0;
    }

    /**
     * Main call to action: the contact section, otherwise e-mail
     *
     * @param object $about
     * @param array $visibility
     * @param string $home
     * @return array|null
     */
    private function cta($about, array $visibility, string $home)
    {
        if ($this->isVisible($visibility, 'contact')) {
            return ['href' => $home . '#contact', 'email' => $about->email ?: null];
        }

        if (!empty($about->email)) {
            return ['href' => 'mailto:' . $about->email, 'email' => $about->email];
        }

        return null;
    }

    /**
     * Text color with the better WCAG contrast on top of the accent color
     *
     * @param string $hex
     * @return string
     */
    private function inkFor(string $hex)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $channels = array_map(function ($pair) {
            $value = hexdec($pair) / 255;

            return $value <= 0.03928 ? $value / 12.92 : pow(($value + 0.055) / 1.055, 2.4);
        }, str_split(substr(str_pad($hex, 6, '0'), 0, 6), 2));

        $luminance = 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];

        $contrastWithDark = ($luminance + 0.05) / 0.05;
        $contrastWithLight = 1.05 / ($luminance + 0.05);

        return $contrastWithDark >= $contrastWithLight ? '#0b0c0e' : '#ffffff';
    }

    /**
     * Custom project buttons stored as JSON. Only http(s) links are returned.
     *
     * @param mixed $value
     * @return array
     */
    private function projectButtons($value)
    {
        $buttons = [];

        foreach ($this->decodeList($value) as $button) {
            if (!is_array($button)) {
                continue;
            }

            $label = trim((string) ($button['label'] ?? ''));
            $url = trim((string) ($button['url'] ?? ''));
            $color = strtolower(trim((string) ($button['color'] ?? '')));

            if ($label === '' || !preg_match('#^https?://#i', $url) || !preg_match('/^#[0-9a-f]{6}$/', $color)) {
                continue;
            }

            $buttons[] = [
                'label' => $label,
                'url' => $url,
                'color' => $color,
                'ink' => $this->inkFor($color),
            ];
        }

        return $buttons;
    }

    /**
     * Decode a JSON list stored in a text column
     *
     * @param mixed $value
     * @return array
     */
    private function decodeList($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = is_string($value) ? json_decode($value, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array $visibility
     * @param string $key
     * @return bool
     */
    private function isVisible(array $visibility, string $key)
    {
        return !empty($visibility[$key]);
    }
}
