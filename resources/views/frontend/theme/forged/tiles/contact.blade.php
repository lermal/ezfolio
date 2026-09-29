<section class="tile" style="{{ $tile['style'] }}" aria-labelledby="forged-contact-title">
    @include('frontend.theme.forged.label', [
        'tag' => 'h2',
        'id' => 'forged-contact-title',
        'number' => $tile['number'],
        'text' => __('forged.tiles.contact'),
    ])

    <p class="status">
        <span class="status__dot" aria-hidden="true"></span>
        {{ __('forged.contact.status') }}
    </p>

    @if ($about->email || $about->phone)
        <div class="contact-lines">
            @if ($about->email)
                <a href="mailto:{{ $about->email }}">{{ $about->email }}</a>
            @endif
            @if ($about->phone)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $about->phone) }}">{{ $about->phone }}</a>
            @endif
        </div>
    @endif

    @if (!empty($forged['socials']))
        <ul class="socials" role="list">
            @foreach ($forged['socials'] as $social)
                @if (!empty($social['link']))
                    <li>
                        <a href="{{ $social['link'] }}" class="icon-btn" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['title'] ?? $social['link'] }}">
                            <i class="{{ $social['iconClass'] ?? 'fas fa-link' }}" aria-hidden="true"></i>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    @endif
</section>
