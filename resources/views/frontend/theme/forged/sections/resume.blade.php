@php($resume = $forged['resume'])
<section id="resume" class="forge-section" aria-labelledby="resume-title">
    @include('frontend.theme.forged.section-head')

    <div class="resume {{ $resume['experiences']->isNotEmpty() ? 'resume--split' : '' }}">
        @if ($resume['experiences']->isNotEmpty())
            <div class="resume__main">
                <h3 class="resume__heading">{{ __('forged.resume.experience') }}</h3>
                <ol class="timeline">
                    @foreach ($resume['experiences'] as $experience)
                        <li class="timeline__item" data-reveal style="--i: {{ $loop->index % 4 }};">
                            <p class="timeline__period">{{ $experience->period }}</p>
                            <p class="timeline__title">{{ $experience->position }}</p>
                            <p class="timeline__place">{{ $experience->company }}</p>
                            @if ($experience->details)
                                <p class="timeline__text">{{ $experience->details }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        <div class="resume__aside">
            @if ($resume['education']->isNotEmpty())
                <div class="tile" data-reveal>
                    <h3 class="resume__heading">{{ __('forged.resume.education') }}</h3>
                    <ul class="grid gap-5" role="list">
                        @foreach ($resume['education'] as $item)
                            <li>
                                <p class="timeline__period">{{ $item->period }}</p>
                                <p class="timeline__title">{{ $item->degree }}</p>
                                <p class="timeline__place">
                                    {{ $item->institution }}@if ($item->department), {{ $item->department }}@endif
                                </p>
                                @if ($item->cgpa || $item->thesis)
                                    <dl class="meta">
                                        @if ($item->cgpa)
                                            <div><dt>{{ __('forged.resume.cgpa') }}</dt><dd>{{ $item->cgpa }}</dd></div>
                                        @endif
                                        @if ($item->thesis)
                                            <div><dt>{{ __('forged.resume.thesis') }}</dt><dd>{{ $item->thesis }}</dd></div>
                                        @endif
                                    </dl>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($resume['skills']->isNotEmpty())
                <div class="tile" data-reveal style="--i: 1;">
                    <h3 class="resume__heading">{{ __('forged.resume.skills') }}</h3>
                    @if ($resume['proficiency'])
                        <ul class="skills" role="list">
                            @foreach ($resume['skills'] as $skill)
                                <li class="skill" style="--p: {{ max(0, min(100, (int) $skill->proficiency)) / 100 }};">
                                    <span>{{ $skill->name }}</span>
                                    <span class="skill__value">{{ (int) $skill->proficiency }}%</span>
                                    <span class="skill__bar" aria-hidden="true"></span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <ul class="chips" role="list">
                            @foreach ($resume['skills'] as $skill)
                                <li class="chip">{{ $skill->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
