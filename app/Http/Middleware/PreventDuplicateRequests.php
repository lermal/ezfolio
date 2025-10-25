<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PreventDuplicateRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Применяем только к POST запросам с капчей
        if ($request->isMethod('POST') && $request->has('cf-turnstile-response')) {
            $key = $this->generateCacheKey($request);
            
            // Проверяем, не был ли уже отправлен такой запрос
            if (Cache::has($key)) {
                Log::warning('Duplicate request detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->url()
                ]);
                
                return response()->json([
                    'message' => 'Дублированный запрос обнаружен. Пожалуйста, подождите несколько секунд и попробуйте снова.',
                    'status' => 429
                ], 429);
            }
            
            // Сохраняем ключ на 30 секунд
            Cache::put($key, true, 30);
        }
        
        return $next($request);
    }
    
    /**
     * Генерируем уникальный ключ для запроса
     *
     * @param Request $request
     * @return string
     */
    private function generateCacheKey(Request $request): string
    {
        $data = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'turnstile_response' => $request->input('cf-turnstile-response'),
            'url' => $request->url()
        ];
        
        return 'duplicate_request:' . md5(serialize($data));
    }
}
