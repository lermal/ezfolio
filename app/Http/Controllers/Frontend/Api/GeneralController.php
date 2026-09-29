<?php

namespace App\Http\Controllers\Frontend\Api;

use App\Http\Controllers\Controller;
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
     * Get all projects
     *
     * @return JsonResponse
     */
    public function getProjects()
    {
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
}
