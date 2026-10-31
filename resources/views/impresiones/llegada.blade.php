@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $inscritos)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Lista de llegada · Retiro N.º ' . $numero . ' ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <table class="tabla-impresion tabla-llegada">
                <thead>
                    <tr>
                        <th class="columna-silla">N.º</th>
                        <th>Nombres y apellidos</th>
                        <th>Documento</th>
                        <th class="columna-edad">Edad</th>
                        <th>Padrino</th>
                        <th class="columna-check">Llegó</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inscritos as $i => $inscrito)
                        <tr>
                            <td class="columna-silla">{{ $loop->iteration }}</td>
                            <td>{{ $inscrito->persona->apellidos }}, {{ $inscrito->persona->nombres }}</td>
                            <td>{{ $inscrito->persona->cedula }}</td>
                            <td class="columna-edad">{{ (int) $inscrito->persona->fecha_nacimiento->diffInYears($retiro->fecha_inicio) }}</td>
                            <td>{{ $inscrito->tiene_padrino ? $inscrito->padrino_nombre : '' }}</td>
                            <td class="columna-check"><span class="casilla-papel">{{ $inscrito->llegada_en ? '✓' : '' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="pie-lista">{{ $inscritos->count() }} personas · impreso el {{ now()->format('d/m/Y g:i a') }}</p>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
