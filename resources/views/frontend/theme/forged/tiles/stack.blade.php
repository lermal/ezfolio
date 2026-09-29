<section class="tile" style="{{ $tile['style'] }}" aria-labelledby="forged-stack-title">
    @include('frontend.theme.forged.label', [
        'tag' => 'h2',
        'id' => 'forged-stack-title',
        'number' => $tile['number'],
        'text' => __('forged.tiles.stack'),
    ])

    <ul class="chips" role="list" @if ($forged['stack']['hidden']->isNotEmpty()) data-stack @endif>
        @foreach ($forged['stack']['shown'] as $skill)
            <li class="chip">{{ $skill->name }}</li>
        @endforeach
        @foreach ($forged['stack']['hidden'] as $skill)
            <li class="chip" data-stack-extra hidden>{{ $skill->name }}</li>
        @endforeach
        @if ($forged['stack']['hidden']->isNotEmpty())
            <li class="chip-more">
                <button type="button" class="chip chip--muted chip--more" data-stack-more aria-expanded="false">
                    {{ __('forged.stack.more', ['count' => $forged['stack']['hidden']->count()]) }}
                </button>
            </li>
        @endif
    </ul>
</section>
