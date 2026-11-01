@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $grupo)
        <section class="pagina">
            @include('impresiones.informes.encabezado', ['cargo' => 'Vicerrector(a)'])

            {{-- Una tabla por equipo con sus dirigentes y la hora en que llegaron --}}
            @foreach ($equipos as $equipo)
                <table class="tabla-impresion tabla-informe tabla-equipo">
                    <caption>{{ $equipo }}</caption>
                    <thead>
                        <tr>
                            <th>Nombre del dirigente</th>
                            <th class="columna-corta">Hora de llegada</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($fila = 0; $fila < 5; $fila++)
                            <tr><td></td><td></td><td></td></tr>
                        @endfor
                    </tbody>
                </table>
            @endforeach

            @include('impresiones.informes.firmas', ['firmas' => ['Vicerrector(a)']])
            @include('impresiones.informes.nota')
        </section>
    @endforeach

@include('partials.footerImpresion')
