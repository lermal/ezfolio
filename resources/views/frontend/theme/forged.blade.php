{{-- Data prepared by App\View\Composers\ForgedComposer is available as $forged --}}
@extends('frontend.layouts.theme')

@section('preloader', '')

@section('favicon')
    <link rel="icon" href="{{ asset('assets/themes/forged/favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('assets/themes/forged/favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('assets/themes/forged/apple-touch-icon.png') }}">
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
        @include('frontend.theme.forged.bento')

        @foreach ($forged['sections'] as $section)
            @include('frontend.theme.forged.sections.' . $section['id'], ['section' => $section])
        @endforeach
    </main>

    @include('frontend.theme.forged.footer')

    @if ($forged['works']->isNotEmpty())
        @include('frontend.theme.forged.project-dialog')
    @endif

    @include('frontend.theme.forged.sticky-cta')
@endsection

@section('scripts')
    <script type="importmap">{!! json_encode($forged['assets']['importMap'], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    <script type="module" src="{{ $forged['assets']['entry'] }}"></script>
@endsection
