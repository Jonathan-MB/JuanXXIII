@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombre = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
        @endphp
        {{--  Para quien está en la puerta: horizontal, letra grande y cada mesa
             en su caja. Puede ocupar varias hojas; una mesa no se parte --}}
        <section class="pagina pagina-horizontal puertas">
            @include('partials.membrete', ['subtitulo' => ($servidores ? 'Directivos y catequistas por mesa' : 'Listado de invitados para puertas') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="banda-grupo">{{ $nombre }} <span>Retiro N.º {{ $numero }}</span></p>

            {{-- Quienes sirven en el retiro también pasan por la puerta: el rectorado del grupo --}}
            @php $rectoradoGrupo = ($rectorados[$sexo] ?? collect())->filter(fn ($miembro) => $miembro->nombre); @endphp
            @if ($rectoradoGrupo->isNotEmpty())
                <div class="caja-mesa-puertas caja-rectorado-puertas">
                    <p class="titulo-caja-puertas">Directivos <span class="grupo-caja-puertas">{{ $nombre }}</span></p>
                    <table class="tabla-impresion tabla-puertas">
                        <tbody>
                            @foreach ($rectoradoGrupo as $miembro)
                                <tr>
                                    <td class="cargo-puertas">{{ $miembro->cargo }}</td>
                                    <td class="nombre-puertas">{{ $miembro->nombre }}</td>
                                    <td class="ciudad-directivo">{{ $miembro->ciudad }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

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
                            {{-- Primero los catequistas de la mesa --}}
                            @php
                                $catequistasMesa = [
                                    'CT' => [$fila['mesa']->catequista_titular, $fila['mesa']->titular_ciudad, $fila['mesa']->titular_barrio],
                                    'CA' => [$fila['mesa']->catequista_auxiliar, $fila['mesa']->auxiliar_ciudad, $fila['mesa']->auxiliar_barrio],
                                ];
                            @endphp
                            @foreach ($catequistasMesa as $cargoMesa => [$catequista, $ciudadCatequista, $barrioCatequista])
                                @if ($catequista)
                                    <tr class="fila-catequista-puertas">
                                        <td class="silla-puertas">{{ $cargoMesa }}</td>
                                        <td class="nombre-puertas">{{ $catequista }} <span>{{ $cargoMesa === 'CT' ? 'Catequista titular' : 'Catequista auxiliar' }}</span></td>
                                        <td>{{ $ciudadCatequista }}</td>
                                        <td>{{ $barrioCatequista }}</td>
                                        <td>—</td>
                                    </tr>
                                @endif
                            @endforeach
                            {{-- En la hoja aparte de directivos y catequistas no van los invitados --}}
                            @foreach ($servidores ? [] : $fila['inscritos'] as $inscrito)
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
            @unless ($servidores)
                <p class="pie-lista">{{ $grupo['mesas']->sum(fn ($fila) => $fila['inscritos']->reject->silla_vacia->count()) }} invitados {{ mb_strtolower($nombre) }}</p>
            @endunless
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>Todavía nadie tiene mesa y silla en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
