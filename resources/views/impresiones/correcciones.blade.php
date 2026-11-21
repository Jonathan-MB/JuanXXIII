@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        <section class="pagina pagina-horizontal">
            @include('partials.membrete', ['subtitulo' => 'Hoja de correcciones · Retiro N.º ' . $numero . ' ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="instruccion">Se corta por mesa (por la línea punteada) y cada catequista recibe la de su mesa. Lo que esté mal se corrige en rojo; lo que falte se escribe. Se devuelve a secretaría.</p>

            @foreach ($grupo['mesas'] as $fila)
                {{-- Cada mesa es un recorte aparte: lleva el aviso y, en el título, el grupo y el retiro --}}
                <div class="recorte-mesa">
                <p class="aviso-catequista">CATEQUISTA: POR FAVOR VERIFICAR QUE USTED Y TODOS LOS DE SU MESA LLENEN LA INFORMACIÓN COMPLETA</p>
                <table class="tabla-impresion bloque-mesa">
                    <caption>Mesa {{ $fila['mesa']->numero }}{{ $fila['mesa']->nombre ? ' · ' . $fila['mesa']->nombre : '' }} <span class="caption-grupo">{{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }} · Retiro N.º {{ $numero }}</span></caption>
                    <thead>
                        <tr>
                            <th class="columna-silla">Silla</th>
                            <th>Nombres y apellidos</th>
                            <th>Documento</th>
                            <th class="columna-fecha">Fecha de nacimiento</th>
                            <th>Ciudad</th>
                            <th>Barrio</th>
                            <th>Celular</th>
                            <th class="columna-talla">Talla</th>
                            <th class="columna-contactos-h3">Contactos de emergencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Los catequistas también llenan sus datos: casillas en blanco para escribir --}}
                        @foreach (['CT' => $fila['mesa']->catequista_titular, 'CA' => $fila['mesa']->catequista_auxiliar] as $cargoMesa => $catequista)
                            <tr class="fila-catequista">
                                <td class="columna-silla">{{ $cargoMesa }}</td>
                                <td>{{ $catequista }}</td>
                                @for ($casilla = 0; $casilla < 7; $casilla++)
                                    <td class="casilla-llenar"></td>
                                @endfor
                            </tr>
                        @endforeach
                        @foreach ($fila['inscritos'] as $inscrito)
                            <tr>
                                <td class="columna-silla">{{ $inscrito->silla }}</td>
                                <td>{{ $inscrito->persona->nombre_completo }}</td>
                                <td>{{ $inscrito->persona->cedula }}</td>
                                <td class="dato-sin-salto">{{ $inscrito->persona->fecha_nacimiento?->format('d/m/Y') }}</td>
                                <td>{{ $inscrito->persona->ciudad }}</td>
                                <td>{{ $inscrito->persona->barrio }}</td>
                                <td class="dato-sin-salto">{{ $inscrito->persona->celular }}</td>
                                <td class="columna-talla">{{ $inscrito->persona->talla_camiseta }}</td>
                                <td class="celda-contactos">
                                    @foreach ($inscrito->contactosEmergencia as $contacto)
                                        <span>{{ $contacto->nombre }} ({{ mb_strtolower($contacto->parentesco) }}) · {{ $contacto->celular }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endforeach
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
