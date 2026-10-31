{{-- Formulario corto de entrada o salida de un artículo --}}
<form class="formulario-movimiento" method="POST" action="{{ route('inventario.movimiento', $articulo) }}">
    @csrf
    <input type="hidden" name="tipo" value="{{ $tipo }}">
    <input type="number" name="cantidad" min="1" {{ $tipo === 'salida' ? 'max=' . $articulo->cantidad : '' }} inputmode="numeric" value="1" aria-label="Cantidad" required>
    <input type="text" name="nota" maxlength="255" placeholder="Nota (opcional)" aria-label="Nota">
    <button type="submit" class="{{ $tipo === 'salida' ? 'boton-salida' : 'boton-entrada' }}">{{ $texto }}</button>
</form>
