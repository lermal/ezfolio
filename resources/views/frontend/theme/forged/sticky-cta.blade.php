@if ($forged['cta'])
    <div class="sticky-cta" data-sticky-cta>
        <a href="{{ $forged['cta']['href'] }}" class="btn-forge">{{ __('forged.cta.write') }}</a>
    </div>
@endif
