@extends('admin.layouts.master')

@section('styles')
    <style>
        :root {
            --z-accent-color: {{$settings['accentColor']}};
        }
    </style>
@endsection

@section('body-content')
    <div id="react-root"></div>
@endsection

@section('scripts')
    <script>
        const settings = @json($settings);
    </script>
    @php
        $adminScript = mix('js/client/admin/roots/app.js');
        $adminScriptPath = public_path(ltrim(parse_url($adminScript, PHP_URL_PATH) ?: $adminScript, '/'));
        $adminScriptVersion = is_file($adminScriptPath) ? filemtime($adminScriptPath) : time();
        $adminScript .= (strpos($adminScript, '?') === false ? '?' : '&') . 'v=' . $adminScriptVersion;
    @endphp
    <script src="{{ $adminScript }}"></script>
@endsection