{{-- Data prepared by App\View\Composers\ForgedComposer is available as $forged --}}
@extends('frontend.theme.forged.layout')

@if ($forged['featured'] && $forged['featured']['thumbnail'])
    @section('seo-image', $forged['featured']['thumbnail'])
@endif

@section('main')
    @include('frontend.theme.forged.bento')

    @foreach ($forged['sections'] as $section)
        @include('frontend.theme.forged.sections.' . $section['id'], ['section' => $section])
    @endforeach
@endsection

@section('after-main')
    @if ($forged['works']->isNotEmpty())
        @include('frontend.theme.forged.project-dialog')
    @endif
@endsection
