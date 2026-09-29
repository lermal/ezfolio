{{--
    Base layout for portfolio themes (see config/themes.php).

    Sections a theme can fill:
        styles           - theme stylesheets and accent color rules
        body-attributes  - attributes of the <body> tag
        preloader        - defaults to common.preloader2, an empty section disables it
        content          - page markup
        scripts          - theme scripts; jQuery is already loaded before them

    The layout provides: SEO meta, favicon, analytics, custom header/footer scripts,
    accent color CSS variables (--accent-color, --accent-color-rgb, --z-accent-color),
    preloader, projects widget bundle (when "projects_widget" is on in config/themes.php),
    Turnstile and the contact form handler for a form with id "contact-me-form".
--}}
@php
    $accentColor = $portfolioConfig['accentColor'];
    $validationLocalePath = 'assets/common/lib/jquery-validation/localization/messages_' . app()->getLocale() . '.min.js';
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
    <meta property="og:title" content="{{ $portfolioConfig['seo']['title'] }}"/>
    <meta property="title" content="{{ $portfolioConfig['seo']['title'] }}"/>
    <meta name="description" content="{{ $portfolioConfig['seo']['description'] }}"/>
    <meta property="og:description" content="{{ $portfolioConfig['seo']['description'] }}"/>
    <meta name="author" content="{{ $portfolioConfig['seo']['author'] }}"/>
    <meta property="og:image" content="{{ asset($portfolioConfig['seo']['image']) }}"/>
    <meta property="og:image:secure_url" content="{{ asset($portfolioConfig['seo']['image']) }}"/>
    <title>{{ $about->name }}</title>
    @hasSection('favicon')
        @yield('favicon')
    @else
        <link rel="shortcut icon" type="image/x-icon" href="{{ Utils::getFavicon() }}">
    @endif

    <link href="{{ asset('assets/common/lib/iziToast/css/iziToast.min.css') }}" rel="stylesheet">
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
    @if (config('services.turnstile.site_key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    @php
        $contactFormConfig = [
            'url' => route('contact-me'),
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
    <script src="{{ asset('assets/common/js/contact-form.js') }}"></script>

    @if (!empty($portfolioConfig['script']['footer']))
        <script>
            {!! $portfolioConfig['script']['footer'] !!}
        </script>
    @endif
    @include('common.pixelTracking')
</body>
</html>
