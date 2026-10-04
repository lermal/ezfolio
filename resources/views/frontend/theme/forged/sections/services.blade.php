<section id="services" class="forge-section" aria-labelledby="services-title">
    @include('frontend.theme.forged.section-head')

    <ul class="services" role="list">
        @foreach ($forged['services'] as $service)
            @include('frontend.theme.forged.service-card', ['index' => $loop->index])
        @endforeach
    </ul>
</section>
