{{-- Blueprint caption "01 / Text"; $tag lets it act as the tile heading --}}
@php($tag = $tag ?? 'p')
<{{ $tag }} @isset($id) id="{{ $id }}" @endisset class="label-mono">
    <span class="label-mono__num" aria-hidden="true">{{ $number }} /</span>
    {{ $text }}
</{{ $tag }}>
