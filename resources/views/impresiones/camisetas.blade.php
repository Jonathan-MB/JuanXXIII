@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero   = $retiro->numeroPara($sexo);
            $porTalla = $conteos[$sexo];
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Camisetas por talla · Retiro N.º ' . $numero . ' ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            {{-- --------------------CONTEO------------------- --}}
            <table class="tabla-impresion tabla-conteo-tallas">
                <thead>
                    <tr>
                        @foreach ($porTalla as $talla => $cantidad)
                            <th>{{ $talla }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach ($porTalla as $talla => $cantidad)
                            <td>{{ $cantidad }}</td>
                        @endforeach
                        <td><strong>{{ $porTalla->sum() }}</strong></td>
                    </tr>
                </tbody>
            </table>

            {{-- --------------------POR MESA------------------- --}}
            <div class="columnas-tallas">
                @foreach ($grupo['mesas'] as $fila)
                    <table class="tabla-impresion bloque-mesa tabla-tallas">
                        <caption>Mesa {{ $fila['mesa']->numero }}{{ $fila['mesa']->nombre ? ' · ' . $fila['mesa']->nombre : '' }}</caption>
                        <tbody>
                            @foreach ($fila['inscritos'] as $inscrito)
                                <tr>
                                    <td class="columna-silla">{{ $inscrito->silla }}</td>
                                    <td>{{ $inscrito->persona->nombre_completo }}</td>
                                    <td class="columna-talla">{{ $inscrito->persona->talla_camiseta }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach

                @if ($grupo['sinPuesto']->isNotEmpty())
                    <table class="tabla-impresion bloque-mesa tabla-tallas">
                        <caption>Sin mesa ni silla</caption>
                        <tbody>
                            @foreach ($grupo['sinPuesto'] as $inscrito)
                                <tr>
                                    <td class="columna-silla">–</td>
                                    <td>{{ $inscrito->persona->nombre_completo }}</td>
                                    <td class="columna-talla">{{ $inscrito->persona->talla_camiseta }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
