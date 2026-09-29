{{-- One dialog for all projects, filled by project-dialog.js from the JSON island below --}}
<dialog id="project-dialog" class="project-dialog" aria-labelledby="project-dialog-title">
    <div class="project-dialog__sheet">
        <div class="project-dialog__media" data-slot="media">
            <img data-slot="cover" alt="" decoding="async">
        </div>

        <div class="project-dialog__body">
            <p class="label-mono" data-slot="categories"></p>
            <h2 id="project-dialog-title" class="project-dialog__title"><span data-slot="title"></span></h2>
            <p class="project-dialog__details" data-slot="details"></p>

            <div data-slot="gallery-wrap">
                <p class="label-mono mb-3">{{ __('forged.projects.gallery') }}</p>
                <div class="gallery" data-slot="gallery" tabindex="0" aria-label="{{ __('forged.projects.gallery') }}"></div>
            </div>

            <div class="project-dialog__actions">
                <a class="btn-forge" data-slot="link" target="_blank" rel="noopener noreferrer">{{ __('forged.projects.visit') }}</a>
                <button type="button" class="btn-forge btn-forge--ghost" data-close>{{ __('forged.projects.close') }}</button>
            </div>
        </div>

        <button type="button" class="icon-btn project-dialog__x" data-close autofocus aria-label="{{ __('forged.projects.close') }}">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>
</dialog>

<script type="application/json" id="forged-projects">{!! json_encode($forged['dialog'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
