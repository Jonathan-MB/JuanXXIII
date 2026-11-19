@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        <section class="pagina pagina-horizontal">
            @include('partials.membrete', ['subtitulo' => 'Hoja de correcciones · Retiro N.º ' . $numero . ' ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="instruccion">Hermano(a) catequista: revisa los datos de tu mesa y corrige en rojo lo que esté mal antes de devolver la hoja a secretaría.</p>

            @foreach ($grupo['mesas'] as $fila)
                <table class="tabla-impresion bloque-mesa">
                    <caption>Mesa {{ $fila['mesa']->numero }}{{ $fila['mesa']->nombre ? ' · ' . $fila['mesa']->nombre : '' }}</caption>
                    <thead>
                        <tr>
                            <th class="columna-silla">Silla</th>
                            <th>Nombres y apellidos</th>
                            <th>Documento</th>
                            <th>Fecha nac.</th>
                            <th>Ciudad</th>
                            <th>Barrio</th>
                            <th>Celular</th>
                            <th>Contactos de emergencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="fila-catequista"><td>CT</td><td colspan="7">{{ $fila['mesa']->catequista_titular }}</td></tr>
                        <tr class="fila-catequista"><td>CA</td><td colspan="7">{{ $fila['mesa']->catequista_auxiliar }}</td></tr>
                        @foreach ($fila['inscritos'] as $inscrito)
                            <tr>
                                <td class="columna-silla">{{ $inscrito->silla }}</td>
                                <td>{{ $inscrito->persona->nombre_completo }}</td>
                                <td>{{ $inscrito->persona->cedula }}</td>
                                <td>{{ $inscrito->persona->fecha_nacimiento->format('d/m/Y') }}</td>
                                <td>{{ $inscrito->persona->ciudad }}</td>
                                <td>{{ $inscrito->persona->barrio }}</td>
                                <td class="dato-sin-salto">{{ $inscrito->persona->celular }}</td>
                                <td class="celda-contactos">
                                    @foreach ($inscrito->contactosEmergencia as $contacto)
                                        <span>{{ $contacto->nombre }} ({{ mb_strtolower($contacto->parentesco) }}) · {{ $contacto->celular }}</span>
                                    @endforeach
                                </td>
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
