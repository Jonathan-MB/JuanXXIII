@include('partials.headImpresion')

    @foreach ($dias as $delDia)
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Rotación del Santísimo · ' . ucfirst($delDia->first()->inicio->locale('es')->translatedFormat('l j \d\e F')) . ' · Retiro ' . $retiro->numero_mujeres . ' M - ' . $retiro->numero_hombres . ' H'])

            <table class="tabla-impresion tabla-santisimo">
                <thead>
                    <tr>
                        <th class="columna-hora">Hora</th>
                        <th>Equipo</th>
                        <th>Actividad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($delDia as $franja)
                        <tr class="{{ $franja->todos ? 'fila-todos' : '' }}">
                            <td class="columna-hora">{{ $franja->inicio->format('g:i a') }} – {{ $franja->fin->format('g:i a') }}</td>
                            <td>{{ $franja->todos ? 'TODOS' : ($franja->equipo?->nombre ?? '') }}</td>
                            <td>{{ $franja->actividad_texto }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endforeach

@include('partials.footerImpresion')
