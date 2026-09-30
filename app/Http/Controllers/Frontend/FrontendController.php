<?php

namespace App\Http\Controllers\Frontend;

use App\Events\FrontendVisited;
use App\Helpers\ThemeRegistry;
use App\Http\Controllers\Controller;
use App\Services\Contracts\FrontendInterface;
use Config;
use CoreConstants;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FrontendController extends Controller
{
    /**
     * @var FrontendInterface
     */
    private $frontend;

    /**
     * Create a new instance
     *
     * @param FrontendInterface $frontend
     * @return void
     */
    public function __construct(FrontendInterface $frontend)
    {
        $this->frontend = $frontend;
    }

    /**
     * Handle request
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $data = $this->pageData();

        if (!is_array($data)) {
            return $data;
        }

        $template = $data['portfolioConfig']['template'];

        // Old dialog links (?project=ID) moved to project pages
        if ($request->filled('project') && ThemeRegistry::hasProjectPages($template)) {
            $project = collect($data['projects'] ?? [])->firstWhere('id', (int) $request->query('project'));

            if ($project && $project->slug) {
                return redirect()->route('project', $project->slug, 301);
            }
        }

        return view(ThemeRegistry::view($template), $data);
    }

    /**
     * Page of a single project
     *
     * @param string $slug
     * @return mixed
     */
    public function project(string $slug)
    {
        $data = $this->pageData();

        if (!is_array($data)) {
            return $data;
        }

        $template = $data['portfolioConfig']['template'];

        if (!ThemeRegistry::hasProjectPages($template)) {
            return redirect()->route('frontend');
        }

        $project = empty($data['portfolioConfig']['visibility']['projects'])
            ? null
            : collect($data['projects'] ?? [])->firstWhere('slug', $slug);

        if (!$project) {
            abort(404);
        }

        $data['project'] = $project;
        $data['seoPage'] = [
            'title' => $project->title . ' — ' . $data['about']->name,
            'description' => Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $project->details))), 160),
            'image' => $project->thumbnail,
            'url' => route('project', $project->slug),
            'type' => 'article',
        ];

        return view(ThemeRegistry::projectView($template), $data);
    }

    /**
     * Data shared by the portfolio pages, or a response when a page can't be shown
     *
     * @return array|mixed
     */
    private function pageData()
    {
        $data = $this->frontend->getAllData();

        if ($data['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
            return response()->view('errors.404', [], 404);
        }

        $data = $data['payload'];
        $data['demoMode'] = Config::get('custom.demo_mode');

        if (empty($data['about'])) {
            return view('errors.custom', ['message' => __('controllers.database_not_propagated')]);
        }

        if ((int)$data['portfolioConfig']['maintenanceMode'] === CoreConstants::TRUE) {
            return response()->view('frontend.maintenance', $data, 503);
        }

        return $data;
    }

    /**
     * Handle pixel tracker
     *
     * @param Request $request
     * @return void
     */
    public function pixelTracker(Request $request)
    {
        if (!empty($request->event) && $request->event == 'page_visit') {
            FrontendVisited::dispatch($request->all());
        }
        
        header('Content-type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
    }
}
