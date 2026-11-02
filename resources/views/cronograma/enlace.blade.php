{{-- Lista de destinos de una tarea: secciones primero, después impresiones --}}
<select name="enlace" {!! isset($id) ? 'id="' . $id . '"' : 'aria-label="Enlace"' !!}>
    <option value="">Sin enlace</option>
    @foreach (collect(\App\Models\TareaCronograma::ENLACES)->groupBy(fn ($enlace, $clave) => str_starts_with($clave, 'imprimir') ? 'Imprimir' : 'Ir a una sección', true) as $grupo => $enlaces)
        <optgroup label="{{ $grupo }}">
            @foreach ($enlaces as $clave => [$texto])
                <option value="{{ $clave }}" {{ $actual === $clave ? 'selected' : '' }}>{{ $texto }}</option>
            @endforeach
        </optgroup>
    @endforeach
</select>
