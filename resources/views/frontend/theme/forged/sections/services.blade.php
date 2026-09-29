<section id="services" class="forge-section" aria-labelledby="services-title">
    @include('frontend.theme.forged.section-head')

    <ul class="services" role="list">
        @foreach ($forged['services'] as $service)
            <li class="tile service" data-reveal style="--i: {{ $loop->index % 3 }};">
                @if ($service->icon)
                    <span class="service__icon" aria-hidden="true"><i class="{{ $service->icon }}"></i></span>
                @endif
                <h3 class="service__title">{{ $service->title }}</h3>
                <p class="service__text">{{ $service->details }}</p>
            </li>
        @endforeach
    </ul>
</section>
