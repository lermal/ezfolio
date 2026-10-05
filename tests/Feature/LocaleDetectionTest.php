<?php

namespace Tests\Feature;

use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LocaleDetectionTest extends TestCase
{
    public function testCookieOverridesCloudflareCountry()
    {
        $this->assertLocalizedRedirect(
            $this->httpGet('/en', ['CF-IPCountry' => 'US'], ['locale' => 'ru']),
            '/'
        );
    }

    public function testSessionOverridesCloudflareCountry()
    {
        $this->withSession(['locale' => 'en'])
            ->withHeader('CF-IPCountry', 'RU')
            ->get('/')
            ->assertRedirect('/en');
    }

    public function testCisCountryRedirectsToRussian()
    {
        $this->assertLocalizedRedirect(
            $this->httpGet('/en', ['CF-IPCountry' => 'KZ']),
            '/'
        );
    }

    public function testEuropeanCountryRedirectsToEnglish()
    {
        $this->withHeader('CF-IPCountry', 'DE')
            ->get('/')
            ->assertRedirect('/en');
    }

    public function testUsCountryRedirectsToEnglish()
    {
        $this->withHeader('CF-IPCountry', 'US')
            ->get('/')
            ->assertRedirect('/en');
    }

    public function testMissingCountryUsesAcceptLanguageEnglish()
    {
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/')
            ->assertRedirect('/en');
    }

    public function testUnknownCountryFallsThroughToAcceptLanguage()
    {
        $this->withHeader('CF-IPCountry', 'XX')
            ->withHeader('Accept-Language', 'en')
            ->get('/')
            ->assertRedirect('/en');
    }

    public function testOtherAcceptLanguageUsesDefaultLocale()
    {
        $this->assertLocalizedRedirect(
            $this->httpGet('/en', ['Accept-Language' => 'de-DE,de;q=0.9']),
            '/'
        );
    }

    public function testManualSwitchStoresTheLocaleCookie()
    {
        $this->get('/locale/en?to=/')
            ->assertRedirect('/en')
            ->assertCookie('locale', 'en');
    }

    public function testManualSwitchDropsTheFrontControllerFromTheReturnPath()
    {
        $this->get('/locale/en?to=/index.php')
            ->assertRedirect('/en');

        $this->get('/locale/ru?to=/index.php/projects/demo-project-1')
            ->assertRedirect('/projects/demo-project-1');
    }

    public function testCrawlerIsNotGeoRedirected()
    {
        $response = $this->withHeader('CF-IPCountry', 'US')
            ->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get('/');

        $this->assertFalse($response->isRedirect());
    }

    /**
     * The package keeps a trailing slash on the hidden default locale.
     *
     * @param  \Illuminate\Testing\TestResponse  $response
     * @param  string  $path
     * @return void
     */
    private function assertLocalizedRedirect($response, $path)
    {
        $response->assertRedirect();
        $this->assertSame(rtrim(url($path), '/'), rtrim($response->headers->get('Location'), '/'));
    }

    /**
     * Routes are registered from the real request, the same way index.php boots.
     * The console kernel used by the test case always registers them at /.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @param  array  $cookies
     * @return \Illuminate\Testing\TestResponse
     */
    private function httpGet($uri, array $headers = [], array $cookies = [])
    {
        if ($cookies !== []) {
            $cookies = $this->sealCookies($cookies);
        }

        $app = require __DIR__.'/../../bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

        $server = ['HTTP_ACCEPT' => 'text/html'];
        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        $request = Request::create($uri, 'GET', [], $cookies, [], $server);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return TestResponse::fromBaseResponse($response);
    }

    /**
     * @param  array  $cookies
     * @return array
     */
    private function sealCookies(array $cookies)
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $sealed = [];
        foreach ($cookies as $name => $value) {
            $sealed[$name] = encrypt(CookieValuePrefix::create($name, $app['encrypter']->getKey()).$value, false);
        }

        Facade::clearResolvedInstances();

        return $sealed;
    }
}
