@include('partials.headImpresion')

    {{--  Una sola lista con hombres y mujeres juntos, en orden de fecha:
         retiristas, directivos y catequistas. Al final, renglones en blanco
         para escribir a mano a quien falte --}}
    @php
        $grupoTexto = match ($sexo) {
            'Masculino' => 'Retiro N.º ' . $retiro->numero_hombres . ' Hombres',
            'Femenino'  => 'Retiro N.º ' . $retiro->numero_mujeres . ' Mujeres',
            default     => 'Retiro ' . $retiro->numero_hombres . ' Hombres y ' . $retiro->numero_mujeres . ' Mujeres',
        };
        $inicioRetiro = $retiro->fecha_inicio->copy()->startOfDay();
        $finRetiro    = $retiro->fecha_fin->copy()->startOfDay();
    @endphp
    <section class="pagina">
        @include('partials.membrete', ['subtitulo' => 'Cumpleaños · ' . $grupoTexto . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

        <p class="grupo-sencilla titulo-cumpleanos">Cumpleaños</p>

        <p class="ayuda-cumpleanos">
            Quienes cumplen años del <strong>{{ $desde->locale('es')->translatedFormat('j \d\e F') }}</strong> al <strong>{{ $hasta->locale('es')->translatedFormat('j \d\e F') }}</strong>
            ({{ \App\Http\Controllers\ImpresionController::DIAS_CUMPLEANOS }} días antes y {{ \App\Http\Controllers\ImpresionController::DIAS_CUMPLEANOS }} días después del retiro).
            Los marcados con ★ cumplen durante el retiro.
        </p>

        <table class="tabla-impresion tabla-cumpleanos">
            <thead>
                <tr>
                    <th class="columna-fecha-cumple">Día</th>
                    <th>Nombre</th>
                    <th>Quién es</th>
                    <th class="columna-grupo-cumple">Grupo</th>
                    <th class="columna-anios-cumple">Cumple</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    @php $enRetiro = $fila['fecha']->between($inicioRetiro, $finRetiro); @endphp
                    <tr class="{{ $enRetiro ? 'cumple-en-retiro' : '' }}">
                        <td class="columna-fecha-cumple">{{ $enRetiro ? '★ ' : '' }}{{ ucfirst($fila['fecha']->locale('es')->translatedFormat('D j M')) }}</td>
                        <td class="nombre-cumple">{{ $fila['nombre'] }}</td>
                        <td>{{ $fila['quien'] }}</td>
                        <td class="columna-grupo-cumple">{{ $fila['sexo'] === 'Femenino' ? 'Mujeres' : 'Hombres' }}</td>
                        <td class="columna-anios-cumple">{{ $fila['anios'] }} años</td>
                    </tr>
                @endforeach
                {{-- En blanco, para escribir a mano a quien no esté --}}
                @for ($renglon = 0; $renglon < 8; $renglon++)
                    <tr class="renglon-cumple">
                        <td></td><td></td><td></td><td></td><td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <p class="pie-lista">{{ $filas->count() }} {{ $filas->count() === 1 ? 'persona' : 'personas' }} · impreso el {{ now()->format('d/m/Y g:i a') }}</p>

        {{-- Para completar en Mesas o en Datos para imprimir --}}
        @if ($sinFecha->isNotEmpty())
            <p class="falta-cumpleanos">Sin cumpleaños anotado (directivos y catequistas): {{ $sinFecha->join(', ') }}.</p>
        @endif
    </section>

@include('partials.footerImpresion')
