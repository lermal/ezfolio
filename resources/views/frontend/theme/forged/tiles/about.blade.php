<section class="tile" style="{{ $tile['style'] }}" aria-labelledby="forged-about-title">
    @include('frontend.theme.forged.label', [
        'tag' => 'h2',
        'id' => 'forged-about-title',
        'number' => $tile['number'],
        'text' => __('forged.tiles.about'),
    ])

    <div class="about-head">
        @if ($about->avatar)
            <div class="about-avatar">
                {!! \App\Helpers\ImageHelper::optimizedImage($about->avatar, $about->name) !!}
            </div>
        @endif
        <div class="min-w-0">
            <p class="font-display text-[15px] font-semibold leading-tight">{{ $about->name }}</p>
            @if ($about->address)
                <p class="mt-1 font-mono text-xs text-forge-muted">{{ $about->address }}</p>
            @endif
        </div>
    </div>

    @if ($about->description)
        <p class="about-text">{{ $about->description }}</p>
    @endif

    @if (!empty($forged['stats']))
        <dl class="stats">
            @foreach ($forged['stats'] as $key => $value)
                <div class="flex flex-col-reverse">
                    <dt class="stats__label">{{ trans_choice('forged.stats.' . $key, $value) }}</dt>
                    <dd class="stats__value">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</section>
