<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Middleware\DetectPreferredLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Remember a manual locale and open the same page in that language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, string $locale)
    {
        if (!app('laravellocalization')->checkLocaleInSupportedLocales($locale)) {
            abort(404);
        }

        $request->session()->put(DetectPreferredLocale::SESSION_KEY, $locale);
        cookie()->queue(cookie(
            DetectPreferredLocale::COOKIE,
            $locale,
            DetectPreferredLocale::COOKIE_MINUTES
        ));

        return redirect()->to(DetectPreferredLocale::urlFor(
            $request,
            $locale,
            $this->returnPath($request->query('to'))
        ));
    }

    /**
     * Only a local path may be carried across the switch.
     *
     * @param  mixed  $to
     * @return string
     */
    private function returnPath($to)
    {
        if (!is_string($to) || !preg_match('#\A/(?!/)[^\s\\\\]*\z#u', $to)) {
            return '/';
        }

        return $to;
    }
}
