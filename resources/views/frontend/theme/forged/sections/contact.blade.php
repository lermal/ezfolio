{{-- Submitted by assets/common/js/contact-form.js (loaded by the layout) --}}
<section id="contact" class="forge-section" aria-labelledby="contact-title">
    <div class="tile contact" data-reveal>
        <div class="contact__intro">
            @include('frontend.theme.forged.section-head')
            <p class="contact__lead">{{ __('forged.contact.lead') }}</p>

            @if ($about->email || $about->phone)
                <div class="contact-lines mt-6">
                    @if ($about->email)
                        <a href="mailto:{{ $about->email }}">{{ $about->email }}</a>
                    @endif
                    @if ($about->phone)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $about->phone) }}">{{ $about->phone }}</a>
                    @endif
                </div>
            @endif
        </div>

        <form id="contact-me-form" class="forge-form" method="post" action="{{ route('contact-me') }}">
            @csrf
            <div class="forge-form__row">
                <div class="field">
                    <label for="cf-name" class="field__label">{{ __('forged.form.name') }}</label>
                    <input id="cf-name" name="name" type="text" class="field__input" autocomplete="name" required>
                </div>
                <div class="field">
                    <label for="cf-email" class="field__label">{{ __('forged.form.email') }}</label>
                    <input id="cf-email" name="email" type="email" class="field__input" autocomplete="email" inputmode="email" required>
                </div>
            </div>
            <div class="field">
                <label for="cf-subject" class="field__label">{{ __('forged.form.subject') }}</label>
                <input id="cf-subject" name="subject" type="text" class="field__input" required>
            </div>
            <div class="field">
                <label for="cf-body" class="field__label">{{ __('forged.form.body') }}</label>
                <textarea id="cf-body" name="body" rows="5" class="field__input" required></textarea>
            </div>

            @include('frontend.partials.turnstile', ['theme' => 'dark'])

            <div>
                <button type="submit" class="btn-forge">{{ __('forged.form.send') }}</button>
            </div>
        </form>
    </div>
</section>
