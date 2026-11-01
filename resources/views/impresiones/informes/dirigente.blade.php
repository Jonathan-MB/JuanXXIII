@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $grupo)
        <section class="pagina">
            @include('impresiones.informes.encabezado', ['cargo' => 'Dirigente responsable'])

            <h2 class="titulo-informe">A. Desarrollo de charlas</h2>
            @foreach ($grupo['charlas'] as $charla)
                <div class="bloque-informe">
                    <h3 class="subtitulo-informe">{{ $charla }}</h3>
                    @include('impresiones.informes.renglones', ['cantidad' => 3])
                </div>
            @endforeach

            <div class="bloque-informe">
                <h2 class="titulo-informe">B. Clausura</h2>
                @include('impresiones.informes.renglones', ['cantidad' => 4])
            </div>

            <div class="bloque-informe">
                <h2 class="titulo-informe">C. Observaciones</h2>
                @include('impresiones.informes.renglones', ['cantidad' => 8])
            </div>

            @include('impresiones.informes.firmas', ['firmas' => ['Dirigente responsable']])
            @include('impresiones.informes.nota')
        </section>
    @endforeach

@include('partials.footerImpresion')
