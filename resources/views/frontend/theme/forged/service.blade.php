{{-- Service page: the short details as the lead, the page text from the admin panel, example projects --}}
@extends('frontend.theme.forged.layout')

@php($page = $forged['servicePage'])
@php($current = $page['service'])

@section('main')
    <nav class="crumbs label-mono" aria-label="{{ __('forged.page.breadcrumbs') }}">
        <ol role="list">
            <li><a href="{{ $forged['home'] }}">{{ __('forged.page.home') }}</a></li>
            <li><a href="{{ $forged['home'] }}#services">{{ __('forged.nav.services') }}</a></li>
            <li aria-current="page">{{ $current->title }}</li>
        </ol>
    </nav>

    <article class="tile service-page" aria-labelledby="service-title">
        @if ($current->icon)
            <span class="service__icon" aria-hidden="true"><i class="{{ $current->icon }}"></i></span>
        @endif

        <h1 id="service-title" class="service-page__title">{{ $current->title }}</h1>
        <p class="service-page__lead">{{ $current->details }}</p>

        @if (!empty($page['blocks']))
            <div class="prose-forge">
                @foreach ($page['blocks'] as $block)
                    @if ($block['type'] === 'h2')
                        <h2>{{ $block['text'] }}</h2>
                    @elseif ($block['type'] === 'ul')
                        <ul>
                            @foreach ($block['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>{{ $block['text'] }}</p>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="service-page__actions">
            @if ($forged['cta'])
                <a href="{{ $forged['cta']['href'] }}" class="btn-forge">{{ __('forged.cta.discuss') }}</a>
            @endif
            <a href="{{ $forged['home'] }}#services" class="btn-forge btn-forge--ghost">{{ __('forged.service.all') }}</a>
        </div>
    </article>

    @if ($page['works']->isNotEmpty())
        <section class="forge-section" aria-labelledby="service-works-title">
            <div class="section-bar">
                <div class="section-head">
                    <p class="label-mono">{{ __('forged.nav.projects') }}</p>
                    <h2 id="service-works-title" class="section-title">{{ __('forged.service.works') }}</h2>
                </div>
            </div>

            <div class="works">
                @foreach ($page['works'] as $work)
                    @include('frontend.theme.forged.work-card')
                @endforeach
            </div>
        </section>
    @endif

    @if ($page['others']->isNotEmpty())
        <section class="forge-section" aria-labelledby="service-others-title">
            <div class="section-head mb-7">
                <p class="label-mono">{{ __('forged.nav.services') }}</p>
                <h2 id="service-others-title" class="section-title">{{ __('forged.service.others') }}</h2>
            </div>

            <ul class="services" role="list">
                @foreach ($page['others'] as $service)
                    @include('frontend.theme.forged.service-card', ['index' => $loop->index])
                @endforeach
            </ul>
        </section>
    @endif
@endsection
