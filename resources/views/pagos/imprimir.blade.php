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
                        <th class="columna-medio">Medio</th>
                        <th class="columna-check">Terminó de pagar</th>
                        <th class="columna-check">No asistió</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inscritos as $inscrito)
                        {{-- Quien no vino o se fue sigue en la lista (sus pagos están ahí), en gris y marcado --}}
                        <tr @class(['fila-fuera' => $inscrito->estaFuera()])>
                            <td class="columna-silla">{{ $loop->iteration }}</td>
                            <td>{{ $inscrito->persona->nombre_completo }}@if ($inscrito->estaFuera())<br><strong class="marca-fuera">{{ $inscrito->estado === \App\Models\Inscripcion::ABANDONO ? 'Se fue' : 'No asistió' }}</strong>@endif</td>
                            <td class="dato-sin-salto">{{ $inscrito->persona->celular }}</td>
                            <td>{{ $inscrito->tiene_padrino ? $inscrito->padrino_nombre : '' }}@if ($inscrito->tiene_padrino && $inscrito->padrino_telefono)<br><span class="dato-sin-salto">{{ $inscrito->padrino_telefono }}</span>@endif</td>
                            <td class="columna-valor">{{ '$' . number_format($inscrito->total_pagado, 0, ',', '.') }}</td>
                            <td class="columna-valor">{{ $inscrito->saldo > 0 ? '$' . number_format($inscrito->saldo, 0, ',', '.') : '—' }}</td>
                            <td class="columna-medio">{{ $inscrito->medio_pago }}</td>
                            {{-- Lo que la app ya sabe sale marcado; lo demás se marca a mano --}}
                            <td class="columna-check"><span class="casilla-papel">{{ $inscrito->estado_pago === 'completo' ? '✔' : '' }}</span></td>
                            <td class="columna-check"><span class="casilla-papel">{{ $inscrito->estado === \App\Models\Inscripcion::NO_ASISTIO ? '✔' : '' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @include('pagos.resumenPapel', ['titulo' => $sexo === 'Femenino' ? 'Mujeres' : 'Hombres', 'lista' => $inscritos])

            @if ($loop->last && $grupos->count() > 1)
                @include('pagos.resumenPapel', ['titulo' => 'Todo el retiro', 'lista' => $grupos->flatten(1)])
            @endif

            <p class="pie-lista">{{ $inscritos->count() }} personas · impreso el {{ now()->format('d/m/Y g:i a') }}</p>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este retiro.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
