@php
    $apartados = [
        'C. Colaboración del dirigente responsable'  => 3,
        'D. Colaboración del vicerrector(a)'         => 3,
        'E. Colaboración del presidente parroquial'  => 3,
        'F. Sacerdotes para la confesión'            => 4,
    ];
@endphp
@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $grupo)
        @php
            //  Una fila por catequista: CT y CA de cada mesa y los CR del
            // rectorado. Sin mesas todavía, filas en blanco.
            $catequistas = $grupo['mesas']->flatMap(fn ($mesa) => [
                ['mesa' => $mesa->numero, 'puesto' => 'CT', 'nombre' => $mesa->catequista_titular],
                ['mesa' => $mesa->numero, 'puesto' => 'CA', 'nombre' => $mesa->catequista_auxiliar],
            ])->concat($grupo['rectorado']->where('cargo', 'Catequista rotativo (CR)')->map(fn ($persona) => [
                'mesa' => '', 'puesto' => 'CR', 'nombre' => $persona->nombre,
            ]));

            if ($grupo['mesas']->isEmpty()) {
                $catequistas = collect(array_fill(0, 22, ['mesa' => '', 'puesto' => '', 'nombre' => '']));
            }
        @endphp
        <section class="pagina">
            @include('impresiones.informes.encabezado', ['cargo' => 'Rector(a)'])

            <h2 class="titulo-informe">A. Nómina de catequistas que no asistieron</h2>
            <table class="tabla-impresion tabla-informe">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Reemplazo</th>
                        <th class="columna-corta">N.º retiro</th>
                        <th class="columna-media">Diócesis</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($fila = 0; $fila < 6; $fila++)
                        <tr><td></td><td></td><td></td><td></td></tr>
                    @endfor
                </tbody>
            </table>

            <h2 class="titulo-informe">B. Actuación de catequistas</h2>
            <table class="tabla-impresion tabla-informe">
                <thead>
                    <tr>
                        <th class="columna-corta">Mesa</th>
                        <th class="columna-corta">Puesto</th>
                        <th>Nombre</th>
                        <th class="columna-corta">Hora de llegada</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($catequistas as $catequista)
                        <tr>
                            <td class="celda-centro">{{ $catequista['mesa'] }}</td>
                            <td class="celda-centro">{{ $catequista['puesto'] }}</td>
                            <td>{{ $catequista['nombre'] }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @foreach ($apartados as $apartado => $renglones)
                <div class="bloque-informe">
                    <h2 class="titulo-informe">{{ $apartado }}</h2>
                    @include('impresiones.informes.renglones', ['cantidad' => $renglones])
                </div>
            @endforeach

            @include('impresiones.informes.firmas', ['firmas' => ['Rector(a)']])

            <div class="bloque-informe">
                <h2 class="titulo-informe">Se adjunta</h2>
                <ul class="lista-adjuntos">
                    <li>Nómina inicial de invitación</li>
                    <li>Programa del retiro</li>
                    <li>Directorio final de invitados</li>
                    <li>Programa de charlas</li>
                    <li>Fichas de los invitados</li>
                    <li>Hoja de control de rotación al Santísimo</li>
                    <li>Hoja de control de rotación en sala</li>
                </ul>
            </div>

            @include('impresiones.informes.nota')
        </section>
    @endforeach

@include('partials.footerImpresion')
