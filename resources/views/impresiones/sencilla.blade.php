@include('partials.headImpresion')

    {{--  Para pasar lista en floristería, secretaría y sobres: pequeña, en dos
         columnas, por mesa y silla y con los directivos arriba. Hombres y
         mujeres nunca en la misma hoja --}}
    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombre = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Lista sencilla · Retiro N.º ' . $numero . ' ' . $nombre . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="grupo-sencilla">{{ $nombre }} <span>Retiro N.º {{ $numero }}</span></p>

            <div class="columnas-sencilla">
                @foreach ($grupo['bloques'] as $bloque)
                    <table class="tabla-impresion tabla-sencilla">
                        <thead>
                            <tr><th colspan="3" class="titulo-bloque-sencilla">{{ $bloque['titulo'] }}</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($bloque['filas'] as [$puesto, $persona])
                                <tr class="{{ in_array($puesto, ['CT', 'CA'], true) ? 'fila-catequista-sencilla' : '' }}">
                                    <td class="puesto-sencilla">{{ $puesto }}</td>
                                    <td>{{ $persona }}</td>
                                    <td class="columna-check"><span class="casilla-papel"></span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            </div>

            <p class="pie-lista">{{ $grupo['invitados'] }} {{ mb_strtolower($nombre) }} · impreso el {{ now()->format('d/m/Y g:i a') }}</p>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
