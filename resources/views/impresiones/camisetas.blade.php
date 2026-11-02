@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero   = $retiro->numeroPara($sexo);
            $porTalla = $conteos[$sexo];
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Camisetas por talla · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            {{-- Bien grande: en Librería las hojas de hombres y de mujeres no se pueden confundir --}}
            <p class="banda-grupo">{{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }} <span>Retiro N.º {{ $numero }}</span></p>

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
                        <caption>Mesa {{ $fila['mesa']->numero }}{{ $fila['mesa']->nombre ? ' · ' . $fila['mesa']->nombre : '' }} <span class="grupo-caption">{{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }}</span></caption>
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
            </div>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
