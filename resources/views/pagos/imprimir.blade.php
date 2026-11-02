@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $inscritos)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Lista de pagos · Ofrenda $' . number_format($retiro->ofrenda, 0, ',', '.') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="banda-grupo">{{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }} <span>Retiro N.º {{ $numero }}</span></p>

            {{-- Las dos últimas columnas se marcan a mano --}}
            <table class="tabla-impresion tabla-pagos-papel">
                <thead>
                    <tr>
                        <th class="columna-silla">N.º</th>
                        <th class="columna-nombre">Nombre</th>
                        <th>Celular</th>
                        <th>Padrino</th>
                        <th class="columna-valor">Pagado</th>
                        <th class="columna-valor">Falta</th>
                        <th class="columna-check">Terminó de pagar</th>
                        <th class="columna-check">No asistió</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inscritos as $inscrito)
                        <tr>
                            <td class="columna-silla">{{ $loop->iteration }}</td>
                            <td>{{ $inscrito->persona->apellidos }}, {{ $inscrito->persona->nombres }}</td>
                            <td class="dato-sin-salto">{{ $inscrito->persona->celular }}</td>
                            <td>{{ $inscrito->tiene_padrino ? $inscrito->padrino_nombre : '' }}</td>
                            <td class="columna-valor">{{ '$' . number_format($inscrito->total_pagado, 0, ',', '.') }}</td>
                            <td class="columna-valor">{{ $inscrito->saldo > 0 ? '$' . number_format($inscrito->saldo, 0, ',', '.') : '—' }}</td>
                            <td class="columna-check"><span class="casilla-papel"></span></td>
                            <td class="columna-check"><span class="casilla-papel"></span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="pie-lista">{{ $inscritos->count() }} personas · impreso el {{ now()->format('d/m/Y g:i a') }}</p>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este retiro.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
