@php
    use App\Models\ActividadBase;
@endphp
@include('partials.headImpresion')

    {{-- Para la carpeta del rector(a): se imprimen dos. El catequista se escribe a mano. --}}
    @foreach ($hojas as $grupo => $jornadas)
        @php
            $sexo      = $grupo === 'mujeres' ? 'Femenino' : 'Masculino';
            $rectorado = $rectorados->get($sexo, collect());
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Rotación al Santísimo en sala · Retiro N.º ' . $retiro->numeroPara($sexo) . ' ' . ucfirst($grupo) . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])
            @include('partials.rectoradoImpresion')

            @foreach ($jornadas as $jornada => $franjas)
                <table class="tabla-impresion tabla-desarrollo tabla-rotacion-sala">
                    <caption>{{ ActividadBase::JORNADAS[$jornada] ?? 'Día ' . ($jornada + 1) }}</caption>
                    <thead>
                        <tr>
                            <th class="columna-hora">Hora</th>
                            <th>Charla</th>
                            <th class="columna-charlista">Catequista</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($franjas as $franja)
                            <tr>
                                <td class="columna-hora">{{ $franja['inicio']->format('g:i') }} – {{ $franja['fin']->format('g:i a') }}</td>
                                <td>{{ $franja['charlas'] }}</td>
                                <td class="columna-charlista"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </section>
    @endforeach

@include('partials.footerImpresion')
