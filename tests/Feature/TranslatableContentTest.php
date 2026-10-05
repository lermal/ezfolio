<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use App\Services\Contracts\ProjectInterface;
use App\Support\LocaleContent;
use CoreConstants;
use Tests\TestCase;

class TranslatableContentTest extends TestCase
{
    /**
     * @var int|null
     */
    private $projectId;

    /**
     * @var int|null
     */
    private $serviceId;

    protected function tearDown(): void
    {
        if ($this->projectId) {
            Project::query()->where('id', $this->projectId)->delete();
        }

        if ($this->serviceId) {
            Service::query()->where('id', $this->serviceId)->delete();
        }

        parent::tearDown();
    }

    public function testStoreKeepsBothLocalesAndEnglishFallsBackToRussian()
    {
        $slug = 'locale-content-' . uniqid();
        $result = app(ProjectInterface::class)->store([
            'title' => ['ru' => 'Корабль', 'en' => 'Shipyard'],
            'slug' => $slug,
            'categories' => ['ru' => ['личное'], 'en' => ['personal']],
            'details' => ['ru' => 'Описание', 'en' => 'Details'],
            'seeder_thumbnail' => 'assets/common/img/projects/demo_project_1_1.png',
            'seeder_images' => ['assets/common/img/projects/demo_project_1_1.png'],
            'buttons' => [
                'ru' => [['label' => 'Сайт', 'url' => 'https://example.com', 'color' => '#e85d04']],
                'en' => [['label' => 'Site', 'url' => 'https://example.com', 'color' => '#e85d04']],
            ],
        ]);

        $this->assertSame(CoreConstants::STATUS_CODE_SUCCESS, $result['status'], json_encode($result));
        $this->projectId = $result['payload']->id;

        $project = Project::query()->findOrFail($this->projectId);

        app()->setLocale('ru');
        $this->assertSame('Корабль', $project->fresh()->title);
        $this->assertSame(['личное'], $project->fresh()->categories);

        app()->setLocale('en');
        $this->assertSame('Shipyard', $project->fresh()->title);
        $this->assertSame(['personal'], $project->fresh()->categories);
        $this->assertSame('Site', $project->fresh()->buttons[0]['label']);

        $project->replaceTranslations('title', LocaleContent::stored([
            'ru' => 'Корабль',
            'en' => '',
        ]));
        $project->replaceTranslations('details', LocaleContent::stored([
            'ru' => 'Описание',
            'en' => '',
        ]));
        $project->save();

        app()->setLocale('en');
        $fresh = $project->fresh();
        $this->assertSame('Корабль', $fresh->title);
        $this->assertSame('Описание', $fresh->details);
    }

    public function testServiceEnglishFallsBackToRussian()
    {
        $service = new Service();
        $service->slug = 'locale-service-' . uniqid();
        $service->icon = 'fas fa-code';
        LocaleContent::assign($service, [
            'title' => ['ru' => 'Разработка', 'en' => ''],
            'details' => ['ru' => 'Коротко', 'en' => ''],
        ]);
        $service->save();
        $this->serviceId = $service->id;

        app()->setLocale('en');
        $fresh = $service->fresh();
        $this->assertSame('Разработка', $fresh->title);
        $this->assertSame('Коротко', $fresh->details);
    }

    public function testPickUsesRussianWhenEnglishIsEmpty()
    {
        app()->setLocale('en');
        $this->assertSame('Привет', LocaleContent::pick(['ru' => 'Привет', 'en' => '']));
        $this->assertSame('Hello', LocaleContent::pick(['ru' => 'Привет', 'en' => 'Hello']));

        app()->setLocale('ru');
        $this->assertSame('Привет', LocaleContent::pick(['ru' => 'Привет', 'en' => 'Hello']));
    }
}
