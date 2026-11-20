@include('partials.headImpresion')

    {{--  Para el PC de Secretaría: se imprime y cada responsable marca a mano
         lo que va haciendo. Lo ya marcado en la aplicación sale con ✓. --}}
    <section class="pagina">
        @include('partials.membrete', ['subtitulo' => 'Lista de tareas' . ($retiro ? ' · Retiro ' . $retiro->numero_mujeres . " M - " . $retiro->numero_hombres . " H · " . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year : '')])

        @forelse ($dias as $dia)
            <table class="tabla-impresion bloque-mesa tabla-tareas">
                <caption>{{ $dia['nombre'] }}</caption>
                <thead>
                    <tr>
                        <th class="columna-check">Hecha</th>
                        <th class="columna-hora">Hora</th>
                        <th>Tarea</th>
                        <th class="columna-responsable">Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dia['tareas'] as $tarea)
                        <tr>
                            <td class="columna-check">
                                @if ($hechas->has($tarea->id))
                                    <strong>✓</strong>
                                @else
                                    <span class="casilla-papel"></span>
                                @endif
                            </td>
                            <td class="columna-hora dato-sin-salto">{{ \Illuminate\Support\Carbon::parse($tarea->hora)->format('g:i a') }}</td>
                            <td>{{ $tarea->descripcion }}</td>
                            <td>{{ $tarea->responsable }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p class="instruccion">Todavía no hay tareas en la lista.</p>
        @endforelse
    </section>

@include('partials.footerImpresion')
