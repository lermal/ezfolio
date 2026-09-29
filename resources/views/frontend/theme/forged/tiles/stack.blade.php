<section class="tile" style="{{ $tile['style'] }}" aria-labelledby="forged-stack-title">
    @include('frontend.theme.forged.label', [
        'tag' => 'h2',
        'id' => 'forged-stack-title',
        'number' => $tile['number'],
        'text' => __('forged.tiles.stack'),
    ])

    <ul class="chips" role="list">
        @foreach ($forged['stack']['shown'] as $skill)
            <li class="chip">{{ $skill->name }}</li>
        @endforeach
        @if ($forged['stack']['rest'])
            <li class="chip chip--muted">{{ __('forged.stack.more', ['count' => $forged['stack']['rest']]) }}</li>
        @endif
    </ul>
</section>
