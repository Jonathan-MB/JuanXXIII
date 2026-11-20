@include('partials.headImpresion')

    {{--  Una tabla por mesa: CT, CA y cada silla con una casilla por lo que
         recibe. La camiseta lleva al lado la talla para no buscarla. Si el
         grupo aún no tiene mesas, salen 10 en blanco para llenar a mano. --}}
    @foreach ($sexos as $sexo)
        @php
            $esMujer  = $sexo === 'Femenino';
            $columnas = $esMujer
                ? ['Camiseta', 'Falda', 'Crucifijo', 'Camándula', 'Cartas', 'Previstos']
                : ['Camiseta', 'Crucifijo', 'Camándula', 'Cartas', 'Previstos'];
            $filas = $grupos[$sexo]['mesas'] ?? collect(range(1, 10))->map(fn ($numero) => ['mesa' => null, 'numero' => $numero, 'inscritos' => collect()]);
        @endphp
        @foreach ($filas->chunk(2) as $par)
            <section class="pagina">
                @include('partials.membrete', ['subtitulo' => 'Entrega de previstos · Retiro N.º ' . $retiro->numeroPara($sexo) . ' ' . ($esMujer ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

                @foreach ($par as $fila)
                    @php
                        $mesa     = $fila['mesa'];
                        $porSilla = $fila['inscritos']->keyBy('silla');
                        $sillas   = $mesa?->cantidad_sillas ?? 8;
                    @endphp
                    <table class="tabla-impresion bloque-mesa tabla-previstos">
                        <caption>Mesa {{ $mesa?->numero ?? $fila['numero'] }}{{ $mesa?->nombre ? ' · ' . $mesa->nombre : '' }}</caption>
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
                            @foreach (['CT' => $mesa?->catequista_titular, 'CA' => $mesa?->catequista_auxiliar] as $cargo => $catequista)
                                <tr class="fila-catequista">
                                    <td class="columna-silla">{{ $cargo }}</td>
                                    <td>{{ $catequista }}</td>
                                    @foreach ($columnas as $columna)
                                        <td class="columna-entrega"><span class="casilla-papel"></span></td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @for ($silla = 1; $silla <= $sillas; $silla++)
                                @php
                                    $persona = $porSilla->get($silla)?->persona;
                                @endphp
                                <tr>
                                    <td class="columna-silla">{{ $silla }}</td>
                                    <td>{{ $persona?->nombre_completo }}</td>
                                    @foreach ($columnas as $columna)
                                        <td class="columna-entrega">
                                            <span class="casilla-papel"></span>
                                            @if ($columna === 'Camiseta' && $persona?->talla_camiseta)
                                                <strong>{{ $persona->talla_camiseta }}</strong>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                @endforeach
            </section>
        @endforeach
    @endforeach

@include('partials.footerImpresion')
