@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombreGrupo = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
        @endphp
        <section class="pagina pagina-horizontal">
            @foreach ($grupo['mesas'] as $fila)
                {{-- Cada mesa es un recorte aparte, con su propio membrete pequeño: al cortarla queda completa --}}
                <div class="recorte-mesa">
                <header class="membrete-mesa">
                    <img class="membrete-mesa-logo" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                    <div class="membrete-mesa-texto">
                        <p class="membrete-mesa-movimiento">Movimiento de Retiros Parroquiales Juan XXIII · Arquidiócesis de Cali</p>
                        <p class="membrete-mesa-detalle">Hoja de correcciones · <strong>Retiro N.º {{ $numero }} {{ $nombreGrupo }}</strong> · {{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</p>
                    </div>
                    <p class="membrete-mesa-numero">Mesa {{ $fila['mesa']->numero }}@if ($fila['mesa']->nombre)<span>{{ $fila['mesa']->nombre }}</span>@endif</p>
                </header>
                <p class="aviso-catequista">CATEQUISTA: POR FAVOR VERIFICAR QUE USTED Y TODOS LOS DE SU MESA LLENEN LA INFORMACIÓN COMPLETA</p>
                <table class="tabla-impresion bloque-mesa">
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
                        {{--  Los catequistas también revisan sus datos: sale lo que ya está en
                             Mesas (cumpleaños, ciudad, barrio, celular) y lo que falta queda en
                             blanco para escribirlo. Documento, talla y contactos no se guardan --}}
                        @php
                            $mesaH3 = $fila['mesa'];
                            $catequistasH3 = [
                                'CT' => [$mesaH3->catequista_titular, $mesaH3->titular_nacimiento, $mesaH3->titular_ciudad, $mesaH3->titular_barrio, $mesaH3->titular_celular],
                                'CA' => [$mesaH3->catequista_auxiliar, $mesaH3->auxiliar_nacimiento, $mesaH3->auxiliar_ciudad, $mesaH3->auxiliar_barrio, $mesaH3->auxiliar_celular],
                            ];
                        @endphp
                        @foreach ($catequistasH3 as $cargoMesa => [$catequista, $nacimiento, $ciudad, $barrio, $celular])
                            @php
                                $casillas = [null, $nacimiento?->format('d/m/Y'), $ciudad, $barrio, $celular, null, null];
                            @endphp
                            <tr class="fila-catequista">
                                <td class="columna-silla">{{ $cargoMesa }}</td>
                                <td>{{ $catequista }}</td>
                                @foreach ($casillas as $dato)
                                    <td class="{{ filled($dato) ? 'dato-sin-salto' : 'casilla-llenar' }}">{{ $dato }}</td>
                                @endforeach
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
