@php
    use App\Models\ActividadBase;
@endphp
@include('partials.headImpresion')

    {{-- Para la carpeta del dirigente y la del rector(a) --}}
    @foreach ($hojas as $grupo => $jornadas)
        @php
            $sexo      = $grupo === 'mujeres' ? 'Femenino' : 'Masculino';
            $rectorado = $rectorados->get($sexo, collect());
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Lista de charlistas · Retiro N.º ' . $retiro->numeroPara($sexo) . ' ' . ucfirst($grupo) . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])
            @include('partials.rectoradoImpresion')

            @foreach ($jornadas as $jornada => $charlas)
                <table class="tabla-impresion tabla-desarrollo tabla-charlistas">
                    <caption>{{ ActividadBase::JORNADAS[$jornada] ?? 'Día ' . ($jornada + 1) }}</caption>
                    <thead>
                        <tr>
                            <th class="columna-hora">Hora</th>
                            <th>Charla</th>
                            <th class="columna-charlista">Charlista</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($charlas as $charla)
                            <tr>
                                <td class="columna-hora">{{ $charla->inicio->format('g:i') }}{{ $charla->fin ? ' – ' . $charla->fin->format('g:i a') : ' ' . $charla->inicio->format('a') }}</td>
                                <td>{{ $charla->nombre }}</td>
                                <td class="columna-charlista">{{ $charla->charlista }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </section>
    @endforeach

@include('partials.footerImpresion')
