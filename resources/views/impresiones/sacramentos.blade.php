@php
    //  La tabla de la dinámica de los sacramentos (sábado), tal como la
    // hoja del movimiento: sacramento, citas, materia, forma, gracia y
    // ministro. Dos copias por hoja oficio horizontal, para cortar por la
    // mitad, con el mismo margen a los dos lados para que la impresora no
    // corte nada.
    $sacramentos = [
        ['Bautismo', ['Mt. 28, 18-19', 'Jn. 3, 15', 'Rm. 8, 14-17', 'Col. 2, 12'], 'Lavar con agua la cabeza o el cuerpo del bautizado', 'Yo te bautizo en el nombre del Padre, del Hijo y del Espíritu Santo', 'Vida sobrenatural. Perdón de todos los pecados. Miembro de la Iglesia, filiación divina', 'Sacerdote, diácono y en caso de emergencia cualquier persona'],
        ['Confirmación', ['Hch. 8, 15-17', 'Hch. 1, 4-5', 'Hch. 2, 1-4'], 'Unción con el crisma en la frente e imposición de manos del obispo', 'Por esta señal recibe el sello del Espíritu Santo', 'Mayor vinculación con la Iglesia. Vigor sobrenatural para vivir la fe y vida sobrenatural. Soldado de Cristo', 'Obispo o sacerdote'],
        ['Eucaristía', ['I Co. 11, 23-25', 'Mt. 26, 26-29', 'Jn. 6, 54-56', 'Jn. 13, 1ss'], 'Consagración del pan y del vino', 'Esto es mi Cuerpo, esta es mi Sangre', 'Unión con Cristo (Jn. 6, 55)', 'Sacerdote'],
        ['Reconciliación', ['Eclo. 4, 21.26', 'Jn. 20, 21-23', 'Hch. 19, 18', 'II Co. 5, 18-20', 'Stgo. 5, 16'], 'Autoacusación del penitente arrepentido', 'Yo te absuelvo de tus pecados en el nombre del Padre, del Hijo y del Espíritu Santo', 'Perdón de los pecados y gracia para mantenerse firme', 'Sacerdote'],
        ['Unción de los enfermos', ['Stgo. 5, 14-16', 'Mc. 6, 13', 'C. de T.: DS 1695'], 'Unción al enfermo con óleo bendecido por el obispo', 'Por esta santa unción y por su bondadosa misericordia te ayude el Señor', 'Perdón de los pecados. Fortalecimiento espiritual. Salud del cuerpo si conviene', 'Sacerdote'],
        ['Orden sacerdotal', ['Hch. 13, 2', 'II Tim. 1, 6', 'Hch. 6, 1-6', 'Tt. 1, 5', 'I Tim. 3, 1', 'Heb. 5, 1-4', 'Jn. 15, 16-20', 'Eclo. 7, 31', 'Malq. 2, 7'], 'Imposición de manos', 'Invocación del obispo', 'Sacerdocio ministerial', 'Obispo'],
        ['Matrimonio', ['Mc. 10, 1-12', 'Ef. 5, 21-33', 'Mt. 19, 1-9', 'I Co. 7, 39-40'], 'Entrega mutua dándose las manos', 'El «sí» de los novios', 'Aumento de la gracia santificante y la gracia de estado', 'Los novios; el sacerdote es solo testigo cualificado'],
    ];
@endphp
@include('partials.headImpresion')
@include('impresiones.cantidad')

    @foreach ($grupos as $grupo => $cantidad)
        @foreach (collect(range(1, $cantidad))->chunk(2) as $par)
            <section class="pagina pagina-sacramentos">
                @foreach ($par as $copia)
                    <article class="sacramentos-copia">
                        <header class="sacramentos-cabecera">
                            <img class="sacramentos-logo" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                            <div class="sacramentos-titulo">
                                <p class="sacramentos-movimiento">Movimiento de Retiros Parroquiales JUAN XXIII</p>
                                <p class="sacramentos-arquidiocesis">Arquidiócesis de Cali</p>
                                <p class="sacramentos-nombre">SACRAMENTOS</p>
                            </div>
                            <img class="sacramentos-virgen" src="{{ asset('img/virgen.png') }}" alt="">
                        </header>

                        <table class="tabla-sacramentos">
                            <colgroup>
                                <col class="columna-sacramento">
                                <col class="columna-citas">
                                <col class="columna-materia">
                                <col class="columna-forma">
                                <col class="columna-gracia">
                                <col class="columna-ministro">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Sacramento</th>
                                    <th>Texto bíblico</th>
                                    <th>Ademán litúrgico<br><small>(materia)</small></th>
                                    <th>Palabras sagradas<br><small>(forma)</small></th>
                                    <th>Gracia concedida</th>
                                    <th>Ministro del sacramento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sacramentos as [$nombre, $citas, $materia, $forma, $gracia, $ministro])
                                    <tr>
                                        <th>{{ $nombre }}</th>
                                        <td><ul>@foreach ($citas as $cita)<li @class(["cita-larga" => mb_strlen($cita) > 15])>{{ $cita }}</li>@endforeach</ul></td>
                                        <td>{{ $materia }}</td>
                                        <td>{{ $forma }}</td>
                                        <td>{{ $gracia }}</td>
                                        <td>{{ $ministro }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </article>
                @endforeach
            </section>
        @endforeach
    @endforeach

@include('partials.footerImpresion')
