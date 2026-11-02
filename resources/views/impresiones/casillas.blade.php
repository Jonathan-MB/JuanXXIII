{{-- Casillas para marcar a mano, cada una con su letra: S/N, H/M --}}
@foreach ($opciones as $opcion)
    <span class="opcion-papel">{{ $opcion }} <span class="casilla-papel"></span></span>
@endforeach
