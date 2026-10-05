<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Services\Contracts\ProjectInterface;
use CoreConstants;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectAudienceVisibilityTest extends TestCase
{
    /**
     * @var array
     */
    private $projectIds = [];

    protected function tearDown(): void
    {
        if ($this->projectIds !== []) {
            Project::query()->whereIn('id', $this->projectIds)->delete();
        }

        parent::tearDown();
    }

    public function testNewProjectIsVisibleToBothAudiencesUntilOneIsUnchecked()
    {
        $project = $this->storeProject('audience-both-' . uniqid());

        $this->assertSame(['ru', 'en'], $project->fresh()->visible_locales);
        $this->assertTrue(Project::visibleForLocale('ru')->whereKey($project->id)->exists());
        $this->assertTrue(Project::visibleForLocale('en')->whereKey($project->id)->exists());
    }

    public function testEmptyOrNullAudienceStaysVisibleEverywhere()
    {
        $project = $this->storeProject('audience-empty-' . uniqid(), ['ru']);

        DB::table('projects')->where('id', $project->id)->update(['visible_locales' => null]);
        $this->assertTrue(Project::visibleForLocale('ru')->whereKey($project->id)->exists());
        $this->assertTrue(Project::visibleForLocale('en')->whereKey($project->id)->exists());

        DB::table('projects')->where('id', $project->id)->update(['visible_locales' => json_encode([])]);
        $this->assertTrue(Project::visibleForLocale('en')->whereKey($project->id)->exists());
    }

    public function testAtLeastOneAudienceIsRequired()
    {
        $slug = 'audience-none-' . uniqid();
        $result = $this->store($slug, []);

        $this->assertSame(CoreConstants::STATUS_CODE_BAD_REQUEST, $result['status']);
        $this->assertNull(Project::query()->where('slug', $slug)->first());
    }

    public function testRussianOnlyProjectIsHiddenFromEnglishListsAndSitemap()
    {
        $slug = 'audience-ru-' . uniqid();
        $project = $this->storeProject($slug, ['ru', 'fr']);

        $this->assertSame(['ru'], $project->fresh()->visible_locales);
        $this->assertTrue(Project::visibleForLocale('ru')->whereKey($project->id)->exists());
        $this->assertFalse(Project::visibleForLocale('en')->whereKey($project->id)->exists());

        $russian = $this->getJson('/api/v1/frontend/projects?locale=ru')->assertOk();
        $english = $this->getJson('/api/v1/frontend/projects?locale=en')->assertOk();
        $referred = $this->withHeader('Referer', 'http://forged.local/en/')
            ->getJson('/api/v1/frontend/projects')
            ->assertOk();

        $this->assertTrue($this->slugs($russian)->contains($slug));
        $this->assertFalse($this->slugs($english)->contains($slug));
        $this->assertFalse($this->slugs($referred)->contains($slug));

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        if (str_contains($xml, '/projects/')) {
            $this->assertStringContainsString('/projects/' . $slug, $xml);
            $this->assertStringNotContainsString('/en/projects/' . $slug, $xml);
        }
    }

    /**
     * @param string $slug
     * @param array|null $locales
     * @return Project
     */
    private function storeProject($slug, array $locales = null)
    {
        $result = $this->store($slug, $locales);
        $this->assertSame(CoreConstants::STATUS_CODE_SUCCESS, $result['status'], json_encode($result));
        $this->projectIds[] = $result['payload']->id;

        return $result['payload'];
    }

    /**
     * @param string $slug
     * @param array|null $locales
     * @return array
     */
    private function store($slug, array $locales = null)
    {
        $data = [
            'title' => ['ru' => 'Аудитория ' . $slug, 'en' => 'Audience ' . $slug],
            'slug' => $slug,
            'categories' => ['ru' => ['личное'], 'en' => ['personal']],
            'details' => ['ru' => 'Описание', 'en' => 'Details'],
            'seeder_thumbnail' => 'assets/common/img/projects/demo_project_1_1.png',
            'seeder_images' => ['assets/common/img/projects/demo_project_1_1.png'],
        ];

        if ($locales !== null) {
            $data['visible_locales'] = $locales;
        }

        return app(ProjectInterface::class)->store($data);
    }

    /**
     * @param \Illuminate\Testing\TestResponse $response
     * @return \Illuminate\Support\Collection
     */
    private function slugs($response)
    {
        return collect($response->json('payload'))->pluck('slug');
    }
}
