@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombre = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
        @endphp
        {{--  Para quien está en la puerta: horizontal, letra grande y una mesa por bloque.
             Si ocupa varias hojas, los títulos de la tabla se repiten arriba --}}
        <section class="pagina pagina-horizontal puertas">
            @include('partials.membrete', ['subtitulo' => 'Listado de invitados para puertas · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="banda-grupo">{{ $nombre }} <span>Retiro N.º {{ $numero }}</span></p>

            <table class="tabla-impresion tabla-puertas">
                <colgroup>
                    <col class="col-mesa-puertas"><col class="col-silla-puertas"><col><col class="col-ciudad-puertas"><col class="col-barrio-puertas"><col class="col-padrino-puertas">
                </colgroup>
                <thead>
                    <tr>
                        <th>Mesa</th>
                        <th>Silla</th>
                        <th>Nombre</th>
                        <th>Ciudad</th>
                        <th>Barrio</th>
                        <th>Padrino</th>
                    </tr>
                </thead>
                @foreach ($grupo['mesas'] as $fila)
                    <tbody class="bloque-puertas">
                        @foreach ($fila['inscritos'] as $inscrito)
                            <tr>
                                @if ($loop->first)
                                    <td class="celda-mesa-puertas" rowspan="{{ $fila['inscritos']->count() }}">
                                        <span class="numero-mesa-puertas">{{ $fila['mesa']->numero }}</span>
                                        @if ($fila['mesa']->nombre)
                                            <span class="nombre-mesa-puertas">{{ $fila['mesa']->nombre }}</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="silla-puertas">{{ $inscrito->silla }}</td>
                                <td class="nombre-puertas">{{ $inscrito->persona->nombre_completo }}</td>
                                <td>{{ $inscrito->persona->ciudad }}</td>
                                <td>{{ $inscrito->persona->barrio }}</td>
                                <td>{{ $inscrito->tiene_padrino ? $inscrito->padrino_nombre : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
            <p class="pie-lista">{{ $grupo['mesas']->sum(fn ($fila) => $fila['inscritos']->count()) }} invitados {{ mb_strtolower($nombre) }}</p>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>Todavía nadie tiene mesa y silla en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
