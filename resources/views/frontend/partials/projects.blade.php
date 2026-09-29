{{-- Mount point of the React projects widget (resources/js/client/frontend/roots/projects.js). Optional: $translations --}}
<div
    id="react-project-root"
    data-accentcolor="{{ $portfolioConfig['accentColor'] }}"
    data-demomode="{{ $demoMode }}"
    @isset($translations) data-translations="{{ json_encode($translations) }}" @endisset
></div>
