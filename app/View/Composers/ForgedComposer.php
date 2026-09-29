<?php

namespace App\View\Composers;

use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Prepares data for the "forged" theme: which bento tiles and sections are shown,
 * their spans and numbering, the project cards and the data of the project dialog.
 */
class ForgedComposer
{
    /**
     * Skills shown in the stack tile, the rest collapses into "+N"
     */
    const STACK_LIMIT = 8;

    /**
     * Every n-th project card is wide, starting with the first one
     */
    const WIDE_CARD_EVERY = 5;

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
        $featured = $works->first();
        $socials = $this->decodeList($about->social_links);
        $cta = $this->cta($about, $visibility);

        $hasContacts = $cta !== null || !empty($about->phone) || !empty($socials);

        $tiles = $this->tiles([
            'featured' => $featured !== null,
            'about' => $this->isVisible($visibility, 'about'),
            'stack' => $skills->isNotEmpty(),
            'contact' => $hasContacts,
        ]);

        $view->with('forged', [
            'taglines' => array_values(array_filter($this->decodeList($about->taglines), 'is_string')),
            'heatInk' => $this->inkFor($config['accentColor']),
            'cta' => $cta,
            'cv' => $this->isVisible($visibility, 'cv') && !empty($about->cv) ? $about->cv : null,
            'featured' => $featured,
            'stats' => $this->stats($data, $visibility),
            'stack' => [
                'shown' => $skills->take(self::STACK_LIMIT),
                'rest' => max(0, $skills->count() - self::STACK_LIMIT),
            ],
            'socials' => $socials,
            'tiles' => $tiles,
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
        ]);
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
                'thumbnail' => $project->thumbnail,
                'categories' => array_values(array_filter($this->decodeList($project->categories), 'is_string')),
                'images' => array_values(array_filter($this->decodeList($project->images), 'is_string')),
                'details' => $project->details,
                'link' => $project->link,
                'style' => ($wide ? '--span-lg: 8; --span-md: 6;' : '--span-lg: 4; --span-md: 3;') . ' --i: ' . ($index % 3) . ';',
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
                'categories' => $work['categories'],
                'cover' => $work['thumbnail'] ? asset($work['thumbnail']) : null,
                'images' => array_map('asset', $work['images']),
                'details' => $work['details'],
                'link' => $work['link'] && preg_match('#^https?://#i', $work['link']) ? $work['link'] : null,
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
     * Ordered first-screen tiles with their grid spans
     *
     * @param array $available tile id => whether it has content
     * @return array
     */
    private function tiles(array $available)
    {
        $tiles = [
            ['id' => 'hero', 'lg' => $available['featured'] ? 7 : 12, 'md' => 6, 'rows' => 2],
        ];

        if ($available['featured']) {
            $tiles[] = ['id' => 'featured', 'lg' => 5, 'md' => 6, 'rows' => 2];
        }

        $small = array_keys(array_filter([
            'about' => $available['about'],
            'stack' => $available['stack'],
            'contact' => $available['contact'],
        ]));

        foreach ($small as $index => $id) {
            $tiles[] = [
                'id' => $id,
                'lg' => intdiv(12, count($small)),
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
     * @return array|null
     */
    private function cta($about, array $visibility)
    {
        if ($this->isVisible($visibility, 'contact')) {
            return ['href' => '#contact', 'email' => $about->email ?: null];
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
