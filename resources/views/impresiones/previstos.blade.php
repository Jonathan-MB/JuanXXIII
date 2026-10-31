@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero   = $retiro->numeroPara($sexo);
            $esMujer  = $sexo === 'Femenino';
            $columnas = $esMujer
                ? ['Camiseta', 'Falda', 'Crucifijo', 'Camándula', 'Cartas', 'Previstos']
                : ['Camiseta', 'Crucifijo', 'Camándula', 'Cartas', 'Previstos'];

            $bloques = $grupo['mesas']->map(fn ($fila) => ['mesa' => $fila['mesa'], 'inscritos' => $fila['inscritos']]);

            if ($grupo['sinPuesto']->isNotEmpty()) {
                $bloques->push(['mesa' => null, 'inscritos' => $grupo['sinPuesto']]);
            }
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Entrega de previstos · Retiro N.º ' . $numero . ' ' . ($esMujer ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year . ' · ' . $retiro->lugar])

            @foreach ($bloques as $bloque)
                <table class="tabla-impresion bloque-mesa tabla-previstos">
                    <caption>{{ $bloque['mesa'] ? 'Mesa ' . $bloque['mesa']->numero . ($bloque['mesa']->nombre ? ' · ' . $bloque['mesa']->nombre : '') : 'Sin mesa ni silla' }}</caption>
                    <thead>
                        <tr>
                            <th class="columna-silla">Silla</th>
                            <th>Apellidos y nombres</th>
                            @foreach ($columnas as $columna)
                                <th class="columna-entrega">{{ $columna }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @if ($bloque['mesa'])
                            @foreach (['CT' => $bloque['mesa']->catequista_titular, 'CA' => $bloque['mesa']->catequista_auxiliar] as $cargo => $catequista)
                                <tr class="fila-catequista">
                                    <td class="columna-silla">{{ $cargo }}</td>
                                    <td>{{ $catequista }}</td>
                                    @foreach ($columnas as $columna)
                                        <td class="columna-entrega"><span class="casilla-papel"></span></td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                        @foreach ($bloque['inscritos'] as $inscrito)
                            <tr>
                                <td class="columna-silla">{{ $inscrito->silla ?? '–' }}</td>
                                <td>{{ $inscrito->persona->apellidos }}, {{ $inscrito->persona->nombres }}</td>
                                @foreach ($columnas as $columna)
                                    <td class="columna-entrega">
                                        {{-- En la camiseta va la talla, para no tener que buscarla --}}
                                        @if ($columna === 'Camiseta')
                                            <span class="casilla-papel"></span> <strong>{{ $inscrito->persona->talla_camiseta }}</strong>
                                        @else
                                            <span class="casilla-papel"></span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
