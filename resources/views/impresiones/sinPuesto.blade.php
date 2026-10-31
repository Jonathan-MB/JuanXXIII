{{-- Quienes todavía no tienen mesa y silla: salen al final para no perderlos --}}
@if ($sinPuesto->isNotEmpty())
    <table class="tabla-impresion bloque-mesa tabla-sin-puesto">
        <caption>Sin mesa ni silla asignada</caption>
        <thead>
            <tr>
                <th>Nombres y apellidos</th>
                <th>Ciudad / barrio</th>
                <th>Teléfono</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sinPuesto as $inscrito)
                <tr>
                    <td>{{ $inscrito->persona->nombre_completo }}</td>
                    <td>{{ $inscrito->persona->ciudad }} / {{ $inscrito->persona->barrio }}</td>
                    <td>{{ $inscrito->persona->celular }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
