{{-- Tiles, their order and spans come from ForgedComposer::tiles() --}}
<div class="bento">
    @foreach ($forged['tiles'] as $tile)
        @include('frontend.theme.forged.tiles.' . $tile['id'], ['tile' => $tile])
    @endforeach
</div>
