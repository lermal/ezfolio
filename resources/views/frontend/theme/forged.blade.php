{{-- Data prepared by App\View\Composers\ForgedComposer is available as $forged --}}
@extends('frontend.layouts.theme')

@section('preloader', '')

@section('styles')
    <link href="{{ asset('assets/common/lib/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/themes/forged/css/forged.css') }}" rel="stylesheet">
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
    <script type="module" src="{{ asset('assets/themes/forged/js/forged.js') }}"></script>
@endsection
