@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $grupo)
        <section class="pagina">
            @include('impresiones.informes.encabezado', ['cargo' => null])

            <table class="tabla-impresion tabla-informe tabla-problematicas">
                <thead>
                    <tr>
                        <th class="columna-corta">N.º</th>
                        <th class="columna-corta">Mesa</th>
                        <th>Problemática</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($numero = 1; $numero <= 20; $numero++)
                        <tr>
                            <td class="celda-centro">{{ $numero }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </section>
    @endforeach

@include('partials.footerImpresion')
