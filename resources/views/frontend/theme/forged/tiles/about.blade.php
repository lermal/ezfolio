<section class="tile tile--about" style="{{ $tile['style'] }}" aria-labelledby="forged-about-title">
    @include('frontend.theme.forged.label', [
        'tag' => 'h2',
        'id' => 'forged-about-title',
        'number' => $tile['number'],
        'text' => __('forged.tiles.about'),
    ])

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
