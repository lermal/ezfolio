<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthorizeSystemLogs
{
    private const SESSION_KEY = 'system_logs_access_until';

    private const ACCESS_MINUTES = 30;

    /**
     * Grant log viewer access for a signed url issued to an authenticated admin,
     * then keep it in the session because the viewer's own links drop the signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasValidSignature()) {
            $request->session()->regenerate();
            $request->session()->put(self::SESSION_KEY, now()->addMinutes(self::ACCESS_MINUTES)->timestamp);

            return redirect()->route('system-logs');
        }

        if ($request->session()->get(self::SESSION_KEY, 0) < now()->timestamp) {
            abort(403);
        }

        return $next($request);
    }
}
