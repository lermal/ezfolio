{{--
    Frame shared by the forged pages: assets, header, footer and the mobile CTA.
    Pages fill "main" and "after-main" (dialogs after the footer) and may set "seo-image".
    Data prepared by App\View\Composers\ForgedComposer is available as $forged.
--}}
@extends('frontend.layouts.theme')

@section('preloader', '')

@section('favicon')
    <link rel="icon" href="{{ asset('assets/themes/forged/favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('assets/themes/forged/favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('assets/themes/forged/apple-touch-icon.png') }}">
@endsection

@section('head')
    @if (app()->getLocale() === 'ru')
        <link rel="preload" href="{{ asset('assets/themes/forged/fonts/unbounded-cyrillic.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="{{ asset('assets/themes/forged/fonts/onest-cyrillic.woff2') }}" as="font" type="font/woff2" crossorigin>
    @endif
    <script type="application/ld+json">{!! json_encode($forged['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endsection

@section('styles')
    <link href="{{ asset('assets/common/lib/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ $forged['assets']['css'] }}" rel="stylesheet">
    <style>
        :root {
            --heat-ink: {{ $forged['heatInk'] }};
        }
    </style>
@endsection

@section('content')
    @include('frontend.theme.forged.header')

    <main id="top" class="forge-shell">
        @yield('main')
    </main>

    @include('frontend.theme.forged.footer')

    @yield('after-main')

    @include('frontend.theme.forged.sticky-cta')
@endsection

@section('scripts')
    <script type="importmap">{!! json_encode($forged['assets']['importMap'], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    <script type="module" src="{{ $forged['assets']['entry'] }}"></script>
@endsection
