@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $grupo)
        <section class="pagina">
            @include('impresiones.informes.encabezado', ['cargo' => 'Vicerrector(a)'])

            <h2 class="titulo-informe">A. Hermanos(as) que no asistieron a sus equipos de apostolado</h2>
            @foreach (['Responsable' => 4, 'Integrante' => 10] as $quien => $filas)
                <table class="tabla-impresion tabla-informe">
                    <thead>
                        <tr>
                            <th>{{ $quien }}</th>
                            <th>Reemplazo</th>
                            <th class="columna-media">Equipo</th>
                            <th class="columna-media">N.º y ciudad del retiro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($fila = 0; $fila < $filas; $fila++)
                            <tr><td></td><td></td><td></td><td></td></tr>
                        @endfor
                    </tbody>
                </table>
            @endforeach

            <h2 class="titulo-informe">B. Actuación del responsable de cada equipo</h2>
            <table class="tabla-impresion tabla-informe">
                <thead>
                    <tr>
                        <th class="columna-media">Equipo</th>
                        <th>Nombre</th>
                        <th class="columna-corta">Hora de llegada</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipos as $equipo)
                        <tr>
                            <td>{{ $equipo }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 class="titulo-informe">C. Accionar de los equipos</h2>
            <table class="tabla-impresion tabla-informe">
                <thead>
                    <tr>
                        <th class="columna-media">Equipo</th>
                        <th>Precios</th>
                        <th>Servicio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipos as $equipo)
                        <tr>
                            <td>{{ $equipo }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="bloque-informe">
                <h2 class="titulo-informe">D. Observaciones</h2>
                @include('impresiones.informes.renglones', ['cantidad' => 5])
            </div>

            @include('impresiones.informes.firmas', ['firmas' => ['Vicerrector(a)']])
            @include('impresiones.informes.nota')
        </section>
    @endforeach

@include('partials.footerImpresion')
