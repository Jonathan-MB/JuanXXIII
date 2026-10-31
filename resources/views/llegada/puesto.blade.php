{{-- Mesa y silla escritas a mano para una persona. Las dos vacías quitan el puesto. --}}
<form class="formulario-puesto" method="POST" action="{{ route('llegada.puesto', $inscrito) }}">
    @csrf
    @method('PATCH')
    <label class="casilla-puesto">
        <span>Mesa</span>
        <input type="number" name="mesa" min="1" max="99" inputmode="numeric" value="{{ $inscrito->mesa?->numero }}" aria-label="Mesa de {{ $inscrito->persona->nombres }}">
    </label>
    <label class="casilla-puesto">
        <span>Silla</span>
        <input type="number" name="silla" min="1" max="{{ \App\Models\Mesa::MAXIMO_SILLAS }}" inputmode="numeric" value="{{ $inscrito->silla }}" aria-label="Silla de {{ $inscrito->persona->nombres }}">
    </label>
    <button type="submit" class="boton-secundario">Guardar</button>
</form>
