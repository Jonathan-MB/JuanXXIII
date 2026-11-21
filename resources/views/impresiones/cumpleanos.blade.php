@include('partials.headImpresion')

    {{--  Hombres y mujeres nunca se mezclan: con «Ambos» sale primero la hoja de
         hombres y después la de mujeres. Trae retiristas, directivos y catequistas --}}
    @foreach ($grupos as $sexo => $filas)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombre = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
            $faltan = $sinFecha[$sexo] ?? collect();
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Cumpleaños · Retiro N.º ' . $numero . ' ' . $nombre . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            <p class="ayuda-cumpleanos">
                Quienes cumplen años del <strong>{{ $desde->locale('es')->translatedFormat('j \d\e F') }}</strong> al <strong>{{ $hasta->locale('es')->translatedFormat('j \d\e F') }}</strong>
                ({{ \App\Http\Controllers\ImpresionController::DIAS_CUMPLEANOS }} días antes y {{ \App\Http\Controllers\ImpresionController::DIAS_CUMPLEANOS }} días después del retiro).
                Los marcados con ★ cumplen durante el retiro.
            </p>

            @if ($filas->isEmpty())
                <p class="vacio-cumpleanos">Nadie de {{ mb_strtolower($nombre) }} cumple años en estos días.</p>
            @else
                <table class="tabla-impresion tabla-cumpleanos">
                    <thead>
                        <tr>
                            <th class="columna-fecha-cumple">Día</th>
                            <th>Nombre</th>
                            <th>Quién es</th>
                            <th class="columna-anios-cumple">Cumple</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filas as $fila)
                            @php $enRetiro = $fila['fecha']->between($retiro->fecha_inicio->copy()->startOfDay(), $retiro->fecha_fin->copy()->startOfDay()); @endphp
                            <tr class="{{ $enRetiro ? 'cumple-en-retiro' : '' }}">
                                <td class="columna-fecha-cumple">{{ $enRetiro ? '★ ' : '' }}{{ ucfirst($fila['fecha']->locale('es')->translatedFormat('D j M')) }}</td>
                                <td class="nombre-cumple">{{ $fila['nombre'] }}</td>
                                <td>{{ $fila['quien'] }}</td>
                                <td class="columna-anios-cumple">{{ $fila['anios'] }} años</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <p class="pie-lista">{{ $filas->count() }} {{ $filas->count() === 1 ? 'persona' : 'personas' }} · impreso el {{ now()->format('d/m/Y g:i a') }}</p>

            {{-- Para completar en Mesas o en Datos para imprimir --}}
            @if ($faltan->isNotEmpty())
                <p class="falta-cumpleanos">Sin cumpleaños anotado (directivos y catequistas): {{ $faltan->pluck('nombre')->join(', ') }}.</p>
            @endif
        </section>
    @endforeach

@include('partials.footerImpresion')
