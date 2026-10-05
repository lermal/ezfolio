<?php

namespace App\Http\Controllers\Frontend\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Contracts\FrontendInterface;
use App\Services\Contracts\MessageInterface;
use CoreConstants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\NewMessage;

class GeneralController extends Controller
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
     * Get all projects for the page that asked. The widget sends ?locale=,
     * and the referer covers an older bundle that does not.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProjects(Request $request)
    {
        app()->setLocale(Project::audienceLocale($request->query('locale') ?: $this->localeFromReferer($request)));

        $result = $this->frontend->getAllProjects();

        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }

    /**
     * Store a new message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['ip_address'] = $request->ip();
        
        $createdAt = now();

        $result = resolve(MessageInterface::class)->store($data);
        if ($result['status'] == CoreConstants::STATUS_CODE_SUCCESS) {
            try {
                event(new NewMessage($data['body'], $data['name'], $data['email'], $data['subject'], $createdAt));
            } catch (\Throwable $th) {
                Log::error('Failed to dispatch new message notification', ['error' => $th->getMessage()]);
            }
        }
        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }

    /**
     * Locale of the portfolio page that loaded the projects widget.
     *
     * @param Request $request
     * @return string|null
     */
    private function localeFromReferer(Request $request)
    {
        $referer = (string) $request->headers->get('referer');

        if ($referer === '') {
            return null;
        }

        $path = parse_url($referer, PHP_URL_PATH) ?: '/';

        return preg_match('#^/en(?:/|$)#', $path) ? 'en' : 'ru';
    }
}
