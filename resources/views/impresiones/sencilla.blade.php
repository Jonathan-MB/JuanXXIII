@include('partials.headImpresion')

    {{--  Para pasar lista en floristería, secretaría y sobres: pequeña, dos
         columnas por hoja. Hombres y mujeres nunca en la misma hoja --}}
    @forelse ($grupos as $sexo => $inscritos)
        @php
            $numero  = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombre  = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
            $paginas = $inscritos->values()->chunk(80);
        @endphp
        @foreach ($paginas as $pagina)
            <section class="pagina">
                @include('partials.membrete', ['subtitulo' => 'Lista sencilla · Retiro N.º ' . $numero . ' ' . $nombre . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

                <p class="para-sencilla">Para: <span></span></p>

                <div class="columnas-sencilla">
                    @foreach ($pagina->chunk(max(1, (int) ceil($pagina->count() / 2))) as $columna)
                        <table class="tabla-impresion tabla-sencilla">
                            <thead>
                                <tr>
                                    <th class="columna-silla">N.º</th>
                                    <th>Nombre</th>
                                    <th class="puesto-sencilla">M – S</th>
                                    <th class="columna-check">✓</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($columna as $i => $inscrito)
                                    <tr>
                                        <td class="columna-silla">{{ $i + 1 }}</td>
                                        <td>{{ $inscrito->persona->nombre_completo }}</td>
                                        <td class="puesto-sencilla">{{ $inscrito->mesa ? $inscrito->mesa->numero . ' – ' . $inscrito->silla : '' }}</td>
                                        <td class="columna-check"><span class="casilla-papel"></span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                </div>

                @if ($loop->last)
                    <p class="pie-lista">{{ $inscritos->count() }} {{ mb_strtolower($nombre) }} · impreso el {{ now()->format('d/m/Y g:i a') }}</p>
                @endif
            </section>
        @endforeach
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
