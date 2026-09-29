<header class="forge-header">
    <div class="forge-shell flex h-full items-center justify-between gap-4">
        <a href="#top" class="wordmark">forged<span class="wordmark__zone">.by</span></a>

        <div class="flex items-center gap-6">
            @if (!empty($forged['sections']))
                <nav class="forge-nav hidden md:flex" aria-label="{{ __('forged.nav.label') }}">
                    @foreach ($forged['sections'] as $section)
                        @continue($section['id'] === 'contact')
                        <a href="#{{ $section['id'] }}">{{ __('forged.nav.' . $section['id']) }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($forged['cta'])
                <a href="{{ $forged['cta']['href'] }}" class="btn-forge btn-forge--ghost hidden !min-h-[40px] md:inline-flex">
                    {{ __('forged.cta.write') }}
                </a>
            @endif
        </div>
    </div>
</header>
