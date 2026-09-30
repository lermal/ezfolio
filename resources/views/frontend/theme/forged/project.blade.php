{{-- Project page. The gallery is rendered here and enhanced by project-page.js with the dialog viewer. --}}
@extends('frontend.theme.forged.layout')

@php($page = $forged['page'])
@php($work = $page['project'])

@section('main')
    <nav class="crumbs label-mono" aria-label="{{ __('forged.page.breadcrumbs') }}">
        <ol role="list">
            <li><a href="{{ $forged['home'] }}">{{ __('forged.page.home') }}</a></li>
            <li><a href="{{ $forged['home'] }}#projects">{{ __('forged.nav.projects') }}</a></li>
            <li aria-current="page">{{ $work['title'] }}</li>
        </ol>
    </nav>

    <article class="project-page" data-project-page data-title="{{ $work['title'] }}" aria-labelledby="project-title">
        <div class="project-dialog__sheet">
            @if (!empty($page['images']))
                <div class="project-dialog__media viewer @if (count($page['images']) < 2) is-single @endif" data-slot="media"
                    role="region" aria-roledescription="carousel" aria-label="{{ __('forged.projects.gallery') }}"
                    data-sources="{{ json_encode($page['images'], JSON_UNESCAPED_SLASHES) }}">
                    <div class="viewer__track" data-slot="track" tabindex="0">
                        @foreach ($page['images'] as $index => $image)
                            <figure class="viewer__slide">
                                <img src="{{ $image }}"
                                    alt="{{ count($page['images']) > 1 ? $work['title'] . ' — ' . ($index + 1) : $work['title'] }}"
                                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                    @if ($index === 0) fetchpriority="high" @endif
                                    decoding="async" draggable="false">
                            </figure>
                        @endforeach
                    </div>

                    <button type="button" class="viewer__btn viewer__btn--prev" data-viewer="prev" aria-label="{{ __('forged.projects.prev') }}">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="viewer__btn viewer__btn--next" data-viewer="next" aria-label="{{ __('forged.projects.next') }}">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="viewer__btn viewer__btn--zoom" data-viewer="zoom"
                        aria-label="{{ __('forged.projects.zoom') }}"
                        data-label-zoom="{{ __('forged.projects.zoom') }}"
                        data-label-unzoom="{{ __('forged.projects.unzoom') }}">
                        <i class="fas fa-expand" aria-hidden="true"></i>
                    </button>
                    <p class="viewer__count label-mono" data-slot="count" aria-live="polite"></p>
                </div>
            @endif

            <div class="project-dialog__body">
                @if (!empty($work['categories']))
                    <p class="label-mono">{{ implode(' · ', $work['categories']) }}</p>
                @endif

                <h1 id="project-title" class="project-dialog__title">{{ $work['title'] }}</h1>

                <div class="viewer-thumbs" data-slot="thumbs" aria-label="{{ __('forged.projects.gallery') }}" hidden></div>

                @if ($work['details'])
                    <p class="project-dialog__details">{{ $work['details'] }}</p>
                @endif

                <div class="project-dialog__actions">
                    @if ($page['link'])
                        <a class="btn-forge" href="{{ $page['link'] }}" target="_blank" rel="noopener">{{ __('forged.projects.visit') }}</a>
                    @endif
                    @foreach ($page['buttons'] as $button)
                        <a class="btn-forge btn-forge--custom" href="{{ $button['url'] }}" target="_blank" rel="noopener"
                            style="--btn-color: {{ $button['color'] }}; --btn-ink: {{ $button['ink'] }};">{{ $button['label'] }}</a>
                    @endforeach
                    <a class="btn-forge btn-forge--ghost" href="{{ $forged['home'] }}#projects">{{ __('forged.page.all_projects') }}</a>
                </div>
            </div>
        </div>
    </article>

    @if ($forged['cta'])
        <section class="tile project-cta" aria-labelledby="project-cta-title">
            <h2 id="project-cta-title" class="project-cta__title">{{ __('forged.page.cta') }}</h2>
            <a href="{{ $forged['cta']['href'] }}" class="btn-forge">{{ __('forged.cta.discuss') }}</a>
        </section>
    @endif

    @if ($page['others']->isNotEmpty())
        <section class="forge-section" aria-labelledby="others-title">
            <div class="section-bar">
                <div class="section-head">
                    <p class="label-mono">{{ __('forged.nav.projects') }}</p>
                    <h2 id="others-title" class="section-title">{{ __('forged.page.others') }}</h2>
                </div>
            </div>

            <div class="works">
                @foreach ($page['others'] as $work)
                    @include('frontend.theme.forged.work-card')
                @endforeach
            </div>
        </section>
    @endif
@endsection
