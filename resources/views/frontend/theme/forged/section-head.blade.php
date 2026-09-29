<div class="section-head">
    @include('frontend.theme.forged.label', ['number' => $section['number'], 'text' => __('forged.sections.' . $section['id'] . '.label')])
    <h2 id="{{ $section['id'] }}-title" class="section-title">{{ __('forged.sections.' . $section['id'] . '.title') }}</h2>
</div>
