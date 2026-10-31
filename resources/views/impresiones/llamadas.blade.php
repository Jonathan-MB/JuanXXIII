@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $retiro->numeroPara($sexo);
            $filas  = $grupo['mesas']->map(fn ($fila) => ['titulo' => 'Mesa ' . $fila['mesa']->numero . ($fila['mesa']->nombre ? ' · ' . $fila['mesa']->nombre : ''), 'inscritos' => $fila['inscritos']]);

            if ($grupo['sinPuesto']->isNotEmpty()) {
                $filas->push(['titulo' => 'Sin mesa ni silla', 'inscritos' => $grupo['sinPuesto']]);
            }
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Llamadas familiares · Retiro N.º ' . $numero . ' ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

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
                                $contactos    = $inscrito->contactosEmergencia;
                                $filasPersona = max(1, $contactos->count());
                            @endphp
                            @for ($i = 0; $i < $filasPersona; $i++)
                                @php
                                    $contacto = $contactos[$i] ?? null;
                                @endphp
                                <tr class="{{ $i === 0 ? '' : 'fila-segundo-contacto' }}">
                                    @if ($i === 0)
                                        <td class="columna-silla" rowspan="{{ $filasPersona }}">{{ $inscrito->silla ?? '–' }}</td>
                                        <td rowspan="{{ $filasPersona }}">{{ $inscrito->persona->nombre_completo }}</td>
                                    @endif
                                    <td>{{ $contacto?->nombre }}</td>
                                    <td>{{ $contacto?->parentesco }}</td>
                                    <td>{{ $contacto?->celular }}</td>
                                    <td>{{ $contacto?->telefono_fijo }}</td>
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
