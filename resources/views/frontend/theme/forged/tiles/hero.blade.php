<section class="tile tile--hero" style="{{ $tile['style'] }}" aria-labelledby="forged-hero-title" data-hero>
    @include('frontend.theme.forged.label', ['number' => $tile['number'], 'text' => $about->address ?: 'forged.by'])

    <div class="hero-id">
        @if ($about->hasCustomAvatar())
            <div class="hero-avatar">
                {!! \App\Helpers\ImageHelper::optimizedImage($about->avatar, $about->name, '', ['loading' => 'eager', 'sizes' => '92px']) !!}
            </div>
        @endif
        <h1 id="forged-hero-title" class="hero-title">{{ $about->name }}</h1>
    </div>

    @if (!empty($forged['taglines']))
        <p class="hero-role">
            <span class="sr-only">{{ implode(' · ', $forged['taglines']) }}</span>
            <span aria-hidden="true" data-taglines="{{ json_encode($forged['taglines']) }}">{{ $forged['taglines'][0] }}</span>
        </p>
    @endif

    @if ($forged['cta'] || $forged['cv'])
        <div class="flex flex-wrap gap-3">
            @if ($forged['cta'])
                <a href="{{ $forged['cta']['href'] }}" class="btn-forge">{{ __('forged.cta.discuss') }}</a>
            @endif
            @if ($forged['cv'])
                <a href="{{ $forged['cv'] }}" class="btn-forge btn-forge--ghost" download>{{ __('forged.cta.cv') }}</a>
            @endif
        </div>
    @endif

    <span class="hero-heatline" aria-hidden="true"></span>
</section>
