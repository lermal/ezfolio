<nav class="forge-locale" aria-label="{{ __('locale.label') }}">
    @foreach (LaravelLocalization::getSupportedLocales() as $code => $locale)
        <a
            href="{{ route('locale.switch', ['locale' => $code, 'to' => request()->getRequestUri()]) }}"
            hreflang="{{ $code }}"
            lang="{{ $code }}"
            @class(['is-active' => app()->getLocale() === $code])
            @if (app()->getLocale() === $code) aria-current="true" @endif
        >{{ strtoupper($code) }}</a>
    @endforeach
</nav>
