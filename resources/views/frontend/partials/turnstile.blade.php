{{-- Turnstile widget for the contact form. Optional: $class for the wrapper, $theme (light, dark, auto) --}}
@if (config('services.turnstile.site_key'))
    <div class="{{ $class ?? '' }}">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="{{ $theme ?? 'light' }}"></div>
    </div>
@endif
