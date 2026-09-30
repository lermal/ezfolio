<article class="tile tile--interactive work" style="{{ $work['style'] }}" data-reveal data-categories="{{ json_encode($work['categories']) }}">
    <a href="{{ $work['url'] }}" class="work-link" data-project-open="{{ $work['id'] }}">
        <div class="work-media" data-vt-media>
            @if ($work['thumbnail'])
                {!! \App\Helpers\ImageHelper::optimizedImage($work['thumbnail'], $work['title'], '', ['decoding' => 'async']) !!}
            @endif
        </div>
        <div class="work-body">
            <{{ $headingTag ?? 'h3' }} class="work-title"><span data-vt-title>{{ $work['title'] }}</span></{{ $headingTag ?? 'h3' }}>
            @if (!empty($work['categories']))
                <p class="label-mono">{{ implode(' · ', $work['categories']) }}</p>
            @endif
        </div>
    </a>
</article>
