@php
    //  Las denominaciones que se cuentan. El Word original empezaba en
    // 50.000, pero ya circulan billetes de 100.000.
    $conteos = [
        'Billetes' => [100000, 50000, 20000, 10000, 5000, 2000, 1000],
        'Monedas'  => [1000, 500, 200, 100, 50],
    ];
@endphp
@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $grupo)
        @php
            $nombreDe = fn ($cargo) => $grupo['rectorado']->firstWhere('cargo', $cargo)?->nombre;
        @endphp
        <section class="pagina">
            @include('impresiones.informes.encabezado', ['cargo' => null])

            <p class="texto-acta">
                En la ciudad de Cali, a los <span class="hueco hueco-corto"></span> días del mes de <span class="hueco"></span> de {{ $retiro->fecha_inicio->year }},
                se realiza la entrega por parte del rector(a) <span class="hueco hueco-nombre">{{ $nombreDe('Rector(a)') }}</span>,
                el vicerrector(a) <span class="hueco hueco-nombre">{{ $nombreDe('Vicerrector(a)') }}</span>
                y el dirigente responsable <span class="hueco hueco-nombre">{{ $nombreDe('Dirigente responsable') }}</span>
                al hermano(a) tesorero(a) <span class="hueco hueco-nombre"></span>
                de la ofrenda recibida de los hermanos(as) invitados(as) del retiro N.º {{ $retiro->numeroPara($sexo) }} de la Arquidiócesis de Cali,
                en billetes y monedas de la siguiente denominación:
            </p>

            <div class="conteo-ofrenda">
                @foreach ($conteos as $tipo => $denominaciones)
                    <table class="tabla-impresion tabla-informe">
                        <caption>{{ $tipo }}</caption>
                        <thead>
                            <tr>
                                <th>Denominación</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($denominaciones as $valor)
                                <tr>
                                    <td class="celda-derecha">$ {{ number_format($valor, 0, ',', '.') }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforeach
                            <tr class="fila-total">
                                <th colspan="2">Total en {{ mb_strtolower($tipo) }}</th>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            </div>

            <p class="total-ofrenda"><strong>Total del dinero recogido en billetes y monedas:</strong> <span class="hueco hueco-largo"></span></p>

            <div class="firmas-acta">
                <div>
                    <p class="titulo-firmas">Entrega</p>
                    @include('impresiones.informes.firmas', ['firmas' => ['Rector(a)', 'Vicerrector(a)', 'Dirigente responsable']])
                </div>
                <div>
                    <p class="titulo-firmas">Recibe</p>
                    <div class="firmas-informe">
                        <div>
                            <span class="linea-firma-informe"></span>
                            Tesorero(a)
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endforeach

@include('partials.footerImpresion')
