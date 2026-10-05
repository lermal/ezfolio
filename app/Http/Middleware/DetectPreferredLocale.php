<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

/**
 * Picks the locale mcamara reads from the session, then lets
 * localeSessionRedirect send the visitor to the matching URL.
 *
 * Priority: manual cookie, then session, then Cloudflare CF-IPCountry,
 * then Accept-Language, then the default locale. A crawler is not
 * geo-redirected, so each language URL stays indexable.
 */
class DetectPreferredLocale
{
    /**
     * Same key LocaleSessionRedirect stores and reads.
     */
    public const SESSION_KEY = 'locale';

    /**
     * Same name LocaleCookieRedirect uses. Written only by the manual switch.
     */
    public const COOKIE = 'locale';

    public const COOKIE_MINUTES = 60 * 24 * 365;

    /**
     * ISO 3166-1 alpha-2. Cloudflare sends these uppercase.
     */
    private const CIS = [
        'RU' => true, 'BY' => true, 'UA' => true, 'KZ' => true, 'UZ' => true,
        'AM' => true, 'AZ' => true, 'KG' => true, 'TJ' => true, 'TM' => true,
        'MD' => true, 'GE' => true,
    ];

    /**
     * EU members plus other European codes. CIS countries above stay Russian
     * even when they are geographically European.
     */
    private const EUROPE = [
        'AT' => true, 'BE' => true, 'BG' => true, 'HR' => true, 'CY' => true,
        'CZ' => true, 'DK' => true, 'EE' => true, 'FI' => true, 'FR' => true,
        'DE' => true, 'GR' => true, 'HU' => true, 'IE' => true, 'IT' => true,
        'LV' => true, 'LT' => true, 'LU' => true, 'MT' => true, 'NL' => true,
        'PL' => true, 'PT' => true, 'RO' => true, 'SK' => true, 'SI' => true,
        'ES' => true, 'SE' => true, 'GB' => true, 'UK' => true, 'CH' => true,
        'NO' => true, 'IS' => true, 'AD' => true, 'AL' => true, 'BA' => true,
        'LI' => true, 'MC' => true, 'ME' => true, 'MK' => true, 'RS' => true,
        'SM' => true, 'VA' => true, 'XK' => true, 'GI' => true, 'FO' => true,
        'AX' => true, 'JE' => true, 'GG' => true, 'IM' => true,
    ];

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $preferred = $this->preferredLocale($request);
        $request->session()->put(self::SESSION_KEY, $preferred);
        app()->setLocale($preferred);

        $explicit = $this->explicitLocale($request);
        $effective = $explicit ?? $this->defaultLocale();

        // Redirect ourselves with the path only. localeSessionRedirect builds the
        // target from the full URL, which on this nginx setup includes index.php.
        // A locale prefix would also be copied into the session and ignore the cookie.
        if ($effective !== $preferred) {
            return redirect()->to(
                self::urlFor($request, $preferred, $this->currentPath($request)),
                302,
                ['Vary' => 'Accept-Language']
            );
        }

        return $next($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    private function preferredLocale(Request $request)
    {
        $cookie = $request->cookie(self::COOKIE);

        if ($this->supported($cookie)) {
            return $cookie;
        }

        $sessionLocale = $request->session()->get(self::SESSION_KEY);

        if ($this->supported($sessionLocale)) {
            return $sessionLocale;
        }

        if ($this->isCrawler($request)) {
            return $this->explicitLocale($request) ?? $this->defaultLocale();
        }

        return $this->detect($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    private function detect(Request $request)
    {
        $country = strtoupper((string) $request->header('CF-IPCountry', ''));

        if ($country !== '' && $country !== 'XX' && $country !== 'T1') {
            if (isset(self::CIS[$country])) {
                return 'ru';
            }

            if ($country === 'US' || isset(self::EUROPE[$country])) {
                return 'en';
            }
        }

        $tag = (string) ($request->getLanguages()[0] ?? '');
        $primary = strtolower((string) preg_replace('/[-_].*$/', '', $tag));

        if ($primary === 'ru' || $primary === 'en') {
            return $primary;
        }

        return $this->defaultLocale();
    }

    /**
     * Localized path on the request host. The URL generator would prefix
     * index.php when nginx exposes the front controller as the base URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  string  $path
     * @return string
     */
    public static function urlFor(Request $request, $locale, $path)
    {
        // nginx exposes the front controller as the request URI on "/".
        // Leaving it in the path turns a switch into /en/index.php, which 404s.
        $path = preg_replace('#^/index\.php#', '', $path) ?: '/';
        if ($path[0] !== '/') {
            $path = '/'.$path;
        }

        $path = preg_replace('#^/(?:ru|en)(?=/|\?|$)#', '', $path) ?: '/';
        if ($path[0] !== '/') {
            $path = '/'.$path;
        }

        $localization = app('laravellocalization');
        $prefix = ($locale === $localization->getDefaultLocale() && $localization->hideDefaultLocaleInURL())
            ? ''
            : '/'.$locale;

        $relative = $prefix.($path === '/' ? '' : $path);

        return $request->getSchemeAndHttpHost().($relative === '' ? '/' : $relative);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    private function currentPath(Request $request)
    {
        $uri = preg_replace('#^/index\.php#', '', $request->getRequestUri()) ?: '/';

        return $uri[0] === '/' ? $uri : '/'.$uri;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    private function explicitLocale(Request $request)
    {
        $segment = $request->segment(1);

        return $this->supported($segment) ? $segment : null;
    }

    /**
     * @param  mixed  $locale
     * @return bool
     */
    private function supported($locale)
    {
        return is_string($locale)
            && $locale !== ''
            && app('laravellocalization')->checkLocaleInSupportedLocales($locale);
    }

    /**
     * @return string
     */
    private function defaultLocale()
    {
        // setLocale() writes the URL locale into config('app.locale'). The package
        // keeps the original default captured before that overwrite.
        return (string) app('laravellocalization')->getDefaultLocale();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function isCrawler(Request $request)
    {
        $agent = $request->userAgent();

        return $agent !== null && $agent !== '' && (new Agent())->isRobot($agent);
    }
}
