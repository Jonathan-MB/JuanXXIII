@php
    use App\Models\SantisimoFranja;
@endphp
@include('partials.headImpresion')

    {{--  Una hoja por día, cada día con su color (viernes azul, sábado
         dorado, domingo vino). Dentro, un separador en cada cambio de
         madrugada, mañana, tarde y noche, para no confundir las horas. --}}
    @foreach ($dias as $delDia)
        @php
            $dia = $delDia->first()->inicio->locale('es');
        @endphp
        <section class="pagina hoja-santisimo dia-semana-{{ $dia->dayOfWeek }}">
            <header class="cabecera-santisimo">
                <img src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                <div>
                    <p class="dato-negrita">Rotación al Santísimo</p>
                    <p>Movimiento de Retiros Parroquiales Juan XXIII · Retiro {{ $retiro->numero_mujeres }} M – {{ $retiro->numero_hombres }} H</p>
                </div>
            </header>

            <div class="banda-dia-santisimo">
                <p class="nombre-dia-santisimo">{{ $dia->translatedFormat('l') }}</p>
                <p class="fecha-dia-santisimo">{{ $dia->translatedFormat('j \d\e F') }}</p>
            </div>

            <table class="tabla-santisimo">
                <colgroup>
                    <col class="columna-hora"><col class="columna-equipo"><col>
                </colgroup>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Equipo</th>
                        <th>Actividad</th>
                    </tr>
                </thead>
                @foreach ($delDia->groupBy('periodo') as $periodo => $franjas)
                    <tbody class="periodo-{{ $periodo }}">
                        <tr class="fila-periodo">
                            <td colspan="3">@include('santisimo.periodo', ['periodo' => $periodo]) {{ SantisimoFranja::PERIODOS[$periodo][0] }}</td>
                        </tr>
                        @foreach ($franjas as $franja)
                            <tr class="{{ $franja->todos ? 'fila-todos' : '' }}">
                                <td>{{ $franja->inicio->format('g:i') }} – {{ $franja->fin->format('g:i a') }}</td>
                                <td class="equipo-santisimo">{{ $franja->todos ? 'TODOS' : ($franja->equipo?->nombre ?? '') }}</td>
                                <td>{{ $franja->actividad_texto }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </section>
    @endforeach

@include('partials.footerImpresion')
