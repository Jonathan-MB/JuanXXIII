@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $inscritos)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Lista de llamado · Retiro N.º ' . $numero . ' ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])
            <p class="ayuda-llamado">Llama a cada persona por su nombre y marca la casilla cuando entre a sala. Si no responde, sigue con la siguiente.</p>

            <table class="tabla-impresion tabla-llamado">
                <thead>
                    <tr>
                        <th class="columna-silla">N.º</th>
                        <th>Nombres y apellidos</th>
                        <th>Ciudad</th>
                        <th class="columna-check">Entró</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inscritos as $inscrito)
                        <tr>
                            <td class="columna-silla">{{ $loop->iteration }}</td>
                            <td class="nombre-llamado">{{ $inscrito->persona->nombres }} {{ $inscrito->persona->apellidos }}</td>
                            <td>{{ $inscrito->persona->ciudad }}</td>
                            <td class="columna-check"><span class="casilla-papel"></span></td>
                        </tr>
                    @endforeach

                    {{-- Renglones para quien llegó y no aparece en la lista --}}
                    @for ($i = 0; $i < 4; $i++)
                        <tr class="renglon-a-mano">
                            <td class="columna-silla">{{ $inscritos->count() + $i + 1 }}</td>
                            <td></td>
                            <td></td>
                            <td class="columna-check"><span class="casilla-papel"></span></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
            <p class="pie-lista">{{ $inscritos->count() }} personas en la lista · impreso el {{ now()->format('d/m/Y g:i a') }}</p>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
