@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $retiro->numeroPara($sexo);
            $filas  = $grupo['mesas']->map(fn ($fila) => ['titulo' => 'Mesa ' . $fila['mesa']->numero . ($fila['mesa']->nombre ? ' · ' . $fila['mesa']->nombre : ''), 'inscritos' => $fila['inscritos']]);
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Llamadas familiares · Retiro N.º ' . $numero . ' ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="grupo-sencilla">{{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }} <span>Retiro N.º {{ $numero }}</span></p>

            @foreach ($filas as $fila)
                <table class="tabla-impresion bloque-mesa tabla-llamadas">
                    <caption>{{ $fila['titulo'] }}</caption>
                    <thead>
                        <tr>
                            <th class="columna-silla">Silla</th>
                            <th>Invitado(a)</th>
                            <th>Contacto</th>
                            <th>Parentesco</th>
                            <th>Celular</th>
                            <th>Fijo</th>
                            <th class="columna-check">Llamó</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fila['inscritos'] as $inscrito)
                            @php
                                //  Los dos contactos de emergencia y, al final, el padrino
                                // de retiro por si ninguno contesta
                                $aLlamar = $inscrito->contactosEmergencia->map(fn ($contacto) => [
                                    'nombre'     => $contacto->nombre,
                                    'parentesco' => $contacto->parentesco,
                                    'celular'    => $contacto->celular,
                                    'fijo'       => $contacto->telefono_fijo,
                                    'padrino'    => false,
                                ])->values();

                                if ($inscrito->tiene_padrino && $inscrito->padrino_nombre) {
                                    $aLlamar->push(['nombre' => $inscrito->padrino_nombre, 'parentesco' => 'Padrino de retiro', 'celular' => $inscrito->padrino_telefono, 'fijo' => null, 'padrino' => true]);
                                }

                                $filasPersona = max(1, $aLlamar->count());
                            @endphp
                            @for ($i = 0; $i < $filasPersona; $i++)
                                @php
                                    $contacto = $aLlamar[$i] ?? null;
                                @endphp
                                <tr class="{{ $i === 0 ? 'fila-nuevo-invitado' : 'fila-segundo-contacto' }} {{ ($contacto['padrino'] ?? false) ? 'fila-padrino' : '' }}">
                                    @if ($i === 0)
                                        <td class="columna-silla" rowspan="{{ $filasPersona }}">{{ $inscrito->silla ?? '–' }}</td>
                                        <td rowspan="{{ $filasPersona }}">{{ $inscrito->persona->nombre_completo }}</td>
                                    @endif
                                    <td>{{ $contacto['nombre'] ?? '' }}</td>
                                    <td>{{ $contacto['parentesco'] ?? '' }}</td>
                                    <td class="dato-sin-salto">{{ $contacto['celular'] ?? '' }}</td>
                                    <td class="dato-sin-salto">{{ $contacto['fijo'] ?? '' }}</td>
                                    <td class="columna-check"><span class="casilla-papel"></span></td>
                                </tr>
                            @endfor
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
