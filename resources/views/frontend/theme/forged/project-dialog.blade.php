{{-- One dialog for all projects, filled by project-dialog.js from the JSON island below --}}
<dialog id="project-dialog" class="project-dialog" aria-labelledby="project-dialog-title">
    <div class="project-dialog__sheet">
        <div class="project-dialog__media viewer" data-slot="media" role="region" aria-roledescription="carousel" aria-label="{{ __('forged.projects.gallery') }}">
            <div class="viewer__track" data-slot="track" tabindex="0"></div>

            <button type="button" class="viewer__btn viewer__btn--prev" data-viewer="prev" aria-label="{{ __('forged.projects.prev') }}">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button type="button" class="viewer__btn viewer__btn--next" data-viewer="next" aria-label="{{ __('forged.projects.next') }}">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
            <button type="button" class="viewer__btn viewer__btn--zoom" data-viewer="zoom"
                aria-label="{{ __('forged.projects.zoom') }}"
                data-label-zoom="{{ __('forged.projects.zoom') }}"
                data-label-unzoom="{{ __('forged.projects.unzoom') }}">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
            <p class="viewer__count label-mono" data-slot="count" aria-live="polite"></p>
        </div>

        <div class="project-dialog__body">
            <p class="label-mono" data-slot="categories"></p>
            <h2 id="project-dialog-title" class="project-dialog__title"><span data-slot="title"></span></h2>

            <div class="viewer-thumbs" data-slot="thumbs" aria-label="{{ __('forged.projects.gallery') }}"></div>

            <p class="project-dialog__details" data-slot="details"></p>

            <div class="project-dialog__actions">
                <a class="btn-forge" data-slot="link" target="_blank" rel="noopener noreferrer">{{ __('forged.projects.visit') }}</a>
                <div class="project-dialog__buttons" data-slot="buttons"></div>
                <button type="button" class="btn-forge btn-forge--ghost" data-close>{{ __('forged.projects.close') }}</button>
            </div>
        </div>

        <button type="button" class="icon-btn project-dialog__x" data-close autofocus aria-label="{{ __('forged.projects.close') }}">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>
</dialog>

<script type="application/json" id="forged-projects">{!! json_encode($forged['dialog'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
