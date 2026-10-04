<li class="tile tile--interactive service" data-reveal style="--i: {{ $index % 3 }};">
    <a href="{{ route('service', $service->slug) }}" class="service-link">
        @if ($service->icon)
            <span class="service__icon" aria-hidden="true"><i class="{{ $service->icon }}"></i></span>
        @endif
        <{{ $headingTag ?? 'h3' }} class="service__title">{{ $service->title }}</{{ $headingTag ?? 'h3' }}>
        <p class="service__text">{{ $service->details }}</p>
        <span class="service__more">{{ __('forged.service.more') }} <span aria-hidden="true">→</span></span>
    </a>
</li>
