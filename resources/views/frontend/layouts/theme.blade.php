{{--
    Base layout for portfolio themes (see config/themes.php).

    Sections a theme can fill:
        head             - extra <head> tags: preloads, structured data
        seo-image        - fallback share image path, used when no SEO image is set in the admin panel
        styles           - theme stylesheets and accent color rules
        body-attributes  - attributes of the <body> tag
        preloader        - defaults to common.preloader2, an empty section disables it
        content          - page markup
        scripts          - theme scripts; jQuery is already loaded before them

    A page other than the home one passes $seoPage (title, description, image, url, type)
    to override the site-wide SEO values.

    The layout provides: SEO meta, favicon, analytics, custom header/footer scripts,
    accent color CSS variables (--accent-color, --accent-color-rgb, --z-accent-color),
    preloader, projects widget bundle (when "projects_widget" is on in config/themes.php),
    Turnstile (loaded when its widget comes close to the viewport) and the contact form
    handler for a form with id "contact-me-form".
--}}
@php
    $accentColor = $portfolioConfig['accentColor'];
    $validationLocalePath = 'assets/common/lib/jquery-validation/localization/messages_' . app()->getLocale() . '.min.js';

    $seoPage = array_filter($seoPage ?? []);
    $seoTitle = trim((string) $portfolioConfig['seo']['title']);
    $seoImage = ($seoPage['image'] ?? null)
        ?: $portfolioConfig['seo']['image']
        ?: trim($__env->yieldContent('seo-image'))
        ?: ($about->hasCustomAvatar() ? $about->avatar : null);
    $seo = array_merge([
        'title' => $seoTitle === '' || \Illuminate\Support\Str::contains($seoTitle, $about->name)
            ? ($seoTitle ?: $about->name)
            : $about->name . ' — ' . $seoTitle,
        'description' => \Illuminate\Support\Str::limit(trim((string) ($portfolioConfig['seo']['description'] ?: $about->description)), 160),
        'image' => $seoImage ? asset($seoImage) : null,
        'url' => url('/'),
        'type' => 'website',
        'locale' => ['ru' => 'ru_RU', 'en' => 'en_US'][app()->getLocale()] ?? str_replace('-', '_', app()->getLocale()),
    ], \Illuminate\Support\Arr::except($seoPage, 'image'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('common.googleAnalytics')
    @if (!empty($portfolioConfig['script']['header']))
        <script>
            {!! $portfolioConfig['script']['header'] !!}
        </script>
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $seo['title'] }}</title>
    @if ($seo['description'])
        <meta name="description" content="{{ $seo['description'] }}">
    @endif
    @if ($portfolioConfig['seo']['author'])
        <meta name="author" content="{{ $portfolioConfig['seo']['author'] }}">
    @endif
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $seo['url'] }}">
    @php
        $hrefLocales = array_keys(LaravelLocalization::getSupportedLocales());
        if (isset($project)) {
            $hrefLocales = $project->audiences();
        }
        $xDefaultLocale = in_array(LaravelLocalization::getDefaultLocale(), $hrefLocales, true)
            ? LaravelLocalization::getDefaultLocale()
            : $hrefLocales[0];
    @endphp
    @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $localeMeta)
        @if (!in_array($localeCode, $hrefLocales, true))
            @continue
        @endif
        <link rel="alternate" hreflang="{{ $localeCode }}" href="{{ \App\Http\Middleware\DetectPreferredLocale::urlFor(request(), $localeCode, request()->getRequestUri()) }}">
        @if ($localeCode !== app()->getLocale())
            <meta property="og:locale:alternate" content="{{ str_replace('-', '_', $localeMeta['regional'] ?? $localeCode) }}">
        @endif
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ \App\Http\Middleware\DetectPreferredLocale::urlFor(request(), $xDefaultLocale, request()->getRequestUri()) }}">
    <meta name="theme-color" content="{{ $accentColor }}">

    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta property="og:site_name" content="{{ $about->name }}">
    <meta property="og:locale" content="{{ $seo['locale'] }}">
    <meta property="og:url" content="{{ $seo['url'] }}">
    <meta property="og:title" content="{{ $seo['title'] }}">
    @if ($seo['description'])
        <meta property="og:description" content="{{ $seo['description'] }}">
    @endif
    @if ($seo['image'])
        <meta property="og:image" content="{{ $seo['image'] }}">
        <meta property="og:image:alt" content="{{ $seo['title'] }}">
    @endif

    <meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    @if ($seo['description'])
        <meta name="twitter:description" content="{{ $seo['description'] }}">
    @endif
    @if ($seo['image'])
        <meta name="twitter:image" content="{{ $seo['image'] }}">
    @endif
    @yield('head')
    @hasSection('favicon')
        @yield('favicon')
    @else
        <link rel="shortcut icon" type="image/x-icon" href="{{ Utils::getFavicon() }}">
    @endif

    {{-- Only the contact form notifications use it --}}
    <link href="{{ asset('assets/common/lib/iziToast/css/iziToast.min.css') }}" rel="stylesheet" media="print" onload="this.media='all'">
    @yield('styles')
    <style>
        :root {
            --accent-color: {{ $accentColor }};
            --accent-color-rgb: {{ Utils::getRgbValue($accentColor) }};
            --z-accent-color: {{ $accentColor }};
        }
    </style>
</head>
<body @yield('body-attributes')>
    @section('preloader')
        @include('common.preloader2')
    @show

    @yield('content')

    <script src="{{ asset('assets/common/lib/jquery/jquery.min.js') }}"></script>
    @yield('scripts')

    <script src="{{ asset('assets/common/lib/iziToast/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('assets/common/lib/jquery-validation/jquery.validate.min.js') }}"></script>
    @if (app()->getLocale() !== 'en' && file_exists(public_path($validationLocalePath)))
        <script src="{{ asset($validationLocalePath) }}"></script>
    @endif
    @if (\App\Helpers\ThemeRegistry::usesProjectsWidget($portfolioConfig['template']))
        <script src="{{ asset('js/client/frontend/roots/projects.js') }}"></script>
    @endif
    @php
        $contactFormConfig = [
            'url' => route('contact-me'),
            'turnstileScript' => config('services.turnstile.site_key') ? 'https://challenges.cloudflare.com/turnstile/v0/api.js' : null,
            'messages' => [
                'sending' => __('frontend.contact.sending'),
                'sent' => __('frontend.contact.message_sent'),
                'failed' => __('frontend.contact.message_failed'),
                'networkError' => __('frontend.contact.network_error'),
            ],
        ];
    @endphp
    <script>
        window.ezfolioContactForm = @json($contactFormConfig);
    </script>
    <script src="{{ asset('assets/common/js/contact-form.js') }}?v={{ filemtime(public_path('assets/common/js/contact-form.js')) }}"></script>

    @if (!empty($portfolioConfig['script']['footer']))
        <script>
            {!! $portfolioConfig['script']['footer'] !!}
        </script>
    @endif
    @include('common.pixelTracking')
</body>
</html>
