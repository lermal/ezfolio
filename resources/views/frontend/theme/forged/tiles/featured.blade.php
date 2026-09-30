@php($project = $forged['featured'])
<article class="tile tile--featured tile--interactive" style="{{ $tile['style'] }}">
    <a href="{{ $project['url'] }}" class="featured-link" data-project-open="{{ $project['id'] }}">
        <div class="featured-media" data-vt-media>
            @if ($project['thumbnail'])
                {!! \App\Helpers\ImageHelper::optimizedImage($project['thumbnail'], $project['title'], '', [
                    'loading' => 'eager',
                    'fetchpriority' => 'high',
                    'decoding' => 'async',
                ]) !!}
            @endif
        </div>

        <div class="featured-body">
            @include('frontend.theme.forged.label', ['number' => $tile['number'], 'text' => __('forged.tiles.featured')])

            <h2 class="featured-title"><span data-vt-title>{{ $project['title'] }}</span></h2>

            @if (!empty($project['categories']))
                <ul class="flex flex-wrap gap-2" role="list">
                    @foreach ($project['categories'] as $category)
                        <li class="chip capitalize">{{ $category }}</li>
                    @endforeach
                </ul>
            @endif

            <span class="featured-more">{{ __('forged.featured.open') }} <span aria-hidden="true">→</span></span>
        </div>
    </a>
</article>
