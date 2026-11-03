@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombre = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
        @endphp
        {{--  Para quien está en la puerta: horizontal, letra grande y cada mesa
             en su caja. Puede ocupar varias hojas; una mesa no se parte --}}
        <section class="pagina pagina-horizontal puertas">
            @include('partials.membrete', ['subtitulo' => 'Listado de invitados para puertas · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="banda-grupo">{{ $nombre }} <span>Retiro N.º {{ $numero }}</span></p>

            {{-- Cada mesa en su propia caja, separada de las demás, para no confundirlas --}}
            @foreach ($grupo['mesas'] as $fila)
                <div class="caja-mesa-puertas">
                    <p class="titulo-caja-puertas">
                        Mesa {{ $fila['mesa']->numero }}
                        @if ($fila['mesa']->nombre)
                            <span>{{ $fila['mesa']->nombre }}</span>
                        @endif
                        <span class="grupo-caja-puertas">{{ $nombre }}</span>
                    </p>
                    <table class="tabla-impresion tabla-puertas">
                        <colgroup>
                            <col class="col-silla-puertas"><col><col class="col-ciudad-puertas"><col class="col-barrio-puertas"><col class="col-padrino-puertas">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Silla</th>
                                <th>Nombre</th>
                                <th>Ciudad</th>
                                <th>Barrio</th>
                                <th>Padrino</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fila['inscritos'] as $inscrito)
                                <tr>
                                    <td class="silla-puertas">{{ $inscrito->silla }}</td>
                                    <td class="nombre-puertas">{{ $inscrito->persona->nombre_completo }}</td>
                                    <td>{{ $inscrito->persona->ciudad }}</td>
                                    <td>{{ $inscrito->persona->barrio }}</td>
                                    <td>{{ $inscrito->tiene_padrino ? $inscrito->padrino_nombre : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
            <p class="pie-lista">{{ $grupo['mesas']->sum(fn ($fila) => $fila['inscritos']->count()) }} invitados {{ mb_strtolower($nombre) }}</p>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>Todavía nadie tiene mesa y silla en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
