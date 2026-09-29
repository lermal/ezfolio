@if ($forged['footer'])
    <footer class="forge-footer">
        <div class="forge-shell flex flex-wrap items-center justify-between gap-4">
            <p>© {{ now()->year }} {{ $about->name }} · {{ __('forged.footer.rights') }}</p>
            <a href="#top">{{ __('forged.footer.top') }} <span aria-hidden="true">↑</span></a>
        </div>
    </footer>
@endif
