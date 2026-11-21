{{-- La casilla de llegada de un invitado. «Llegó» la cambia sin recargar la página (llegada.js) --}}
@if ($inscrito->llegada_en)
    <span class="chip chip-verde">Llegó {{ $inscrito->llegada_en->format('g:i a') }}</span>
    <form method="POST" action="{{ route('llegada.deshacer', $inscrito) }}" class="formulario-confirmar" data-confirmar="¿Deshacer la llegada de {{ $inscrito->persona->nombres }}? Su puesto se conserva.">
        @csrf
        @method('PATCH')
        <button type="submit" class="boton-texto boton-deshacer">Deshacer</button>
    </form>
@else
    <form method="POST" action="{{ route('llegada.marcar', $inscrito) }}" class="formulario-llego">
        @csrf
        <button type="submit" class="boton-amarillo boton-llego">Llegó</button>
    </form>
    @if ($inscrito->estado_pago !== 'completo')
        <p class="aviso-saldo">Le falta {{ '$' . number_format($inscrito->saldo, 0, ',', '.') }}</p>
    @endif
@endif
