@php
    use App\Models\ActividadBase;
@endphp
@include('partials.headImpresion')

    @foreach ($hojas as $grupo => $jornadas)
        @php
            $sexo      = $grupo === 'mujeres' ? 'Femenino' : 'Masculino';
            $numero    = $retiro->numeroPara($sexo);
            $rectorado = $rectorados->get($sexo, collect());
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Desarrollo del retiro · Retiro N.º ' . $numero . ' ' . ucfirst($grupo) . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])

            @include('partials.rectoradoImpresion')

            @foreach ($jornadas as $jornada => $delDia)
                <table class="tabla-impresion tabla-desarrollo">
                    <caption>{{ ActividadBase::JORNADAS[$jornada] ?? 'Día ' . ($jornada + 1) }}</caption>
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th class="columna-hora">Hora</th>
                            <th>Desarrollo y conferencistas</th>
                            <th class="columna-tiempo">Tiempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($delDia as $actividad)
                            <tr class="{{ $actividad->es_charla ? 'fila-charla' : '' }}">
                                <td>{{ $actividad->nombre }}</td>
                                <td class="columna-hora">{{ $actividad->inicio->format('g:i') }}{{ $actividad->fin ? ' – ' . $actividad->fin->format('g:i a') : ' ' . $actividad->inicio->format('a') }}</td>
                                <td>{{ $actividad->charlista }}</td>
                                <td class="columna-tiempo">{{ $actividad->duracion ? $actividad->duracion . ' min' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </section>
    @endforeach

@include('partials.footerImpresion')
