@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Hoja de correcciones · Retiro N.º ' . $numero . ' ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="instruccion">Hermano(a) catequista: revisa los datos de tu mesa y corrige en rojo lo que esté mal antes de devolver la hoja a secretaría.</p>

            @foreach ($grupo['mesas'] as $fila)
                <table class="tabla-impresion bloque-mesa">
                    <caption>Mesa {{ $fila['mesa']->numero }}{{ $fila['mesa']->nombre ? ' · ' . $fila['mesa']->nombre : '' }}</caption>
                    <thead>
                        <tr>
                            <th class="columna-silla">Silla</th>
                            <th>Nombres y apellidos</th>
                            <th>Ciudad</th>
                            <th>Barrio</th>
                            <th>Teléfono</th>
                            <th>Fecha nac.</th>
                            <th>Contacto de emergencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="fila-catequista"><td>CT</td><td colspan="6">{{ $fila['mesa']->catequista_titular }}</td></tr>
                        <tr class="fila-catequista"><td>CA</td><td colspan="6">{{ $fila['mesa']->catequista_auxiliar }}</td></tr>
                        @foreach ($fila['inscritos'] as $inscrito)
                            @php
                                $contacto = $inscrito->contactosEmergencia->first();
                            @endphp
                            <tr>
                                <td class="columna-silla">{{ $inscrito->silla }}</td>
                                <td>{{ $inscrito->persona->nombre_completo }}</td>
                                <td>{{ $inscrito->persona->ciudad }}</td>
                                <td>{{ $inscrito->persona->barrio }}</td>
                                <td>{{ $inscrito->persona->celular }}</td>
                                <td>{{ $inscrito->persona->fecha_nacimiento->format('d/m/Y') }}</td>
                                <td>{{ $contacto ? $contacto->nombre . ' (' . $contacto->parentesco . ') ' . $contacto->celular : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            @include('impresiones.sinPuesto', ['sinPuesto' => $grupo['sinPuesto']])
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
