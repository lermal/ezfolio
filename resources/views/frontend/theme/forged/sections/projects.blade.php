<section id="projects" class="forge-section" aria-labelledby="projects-title">
    <div class="section-bar">
        @include('frontend.theme.forged.section-head')

        @if ($forged['categories']->count() > 1)
            <div class="filter" role="group" aria-label="{{ __('forged.projects.filter') }}" data-filter-group>
                <button type="button" class="chip chip--button" data-filter="" aria-pressed="true">{{ __('forged.projects.all') }}</button>
                @foreach ($forged['categories'] as $category)
                    <button type="button" class="chip chip--button capitalize" data-filter="{{ $category }}" aria-pressed="false">{{ $category }}</button>
                @endforeach
            </div>
        @endif
    </div>

    <div class="works" data-works>
        @foreach ($forged['works'] as $work)
            <article class="tile tile--interactive work" style="{{ $work['style'] }}" data-reveal data-categories="{{ json_encode($work['categories']) }}">
                <a href="?project={{ $work['id'] }}" class="work-link" data-project-open="{{ $work['id'] }}">
                    <div class="work-media" data-vt-media>
                        @if ($work['thumbnail'])
                            {!! \App\Helpers\ImageHelper::optimizedImage($work['thumbnail'], $work['title'], '', ['decoding' => 'async']) !!}
                        @endif
                    </div>
                    <div class="work-body">
                        <h3 class="work-title"><span data-vt-title>{{ $work['title'] }}</span></h3>
                        @if (!empty($work['categories']))
                            <p class="label-mono">{{ implode(' · ', $work['categories']) }}</p>
                        @endif
                    </div>
                </a>
            </article>
        @endforeach
    </div>
</section>
