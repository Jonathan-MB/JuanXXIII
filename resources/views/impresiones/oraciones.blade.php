@php
    $fecha = $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year;
@endphp
@include('partials.headImpresion')
@include('impresiones.cantidad')

    {{--  Dos copias por hoja carta, para recortar por la mitad. El texto es
         el de la hoja de oraciones del movimiento. --}}
    @foreach ($grupos as $grupo => $cantidad)
        @php
            $numero = $grupo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        @foreach (collect(range(1, $cantidad))->chunk(2) as $par)
            <section class="pagina pagina-carta hoja-oraciones">
                @foreach ($par as $copia)
                    <article class="oracion-copia">
                        <header class="oracion-cabecera">
                            <img class="oracion-logo" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                            <div class="oracion-titulo">
                                <p>ARQUIDIÓCESIS DE CALI</p>
                                <p>MOVIMIENTO DE RETIROS PARROQUIALES JUAN XXIII</p>
                            </div>
                            <img class="oracion-virgen" src="{{ asset('img/virgen.png') }}" alt="">
                        </header>

                        <p class="oracion-datos">
                            <span><strong>RETIRO No:</strong> {{ $numero }}</span>
                            <span><strong>LUGAR:</strong> {{ mb_strtoupper($retiro->lugar) }}</span>
                            <span><strong>FECHA:</strong> {{ $fecha }}</span>
                        </p>

                        <div class="oracion-columnas">
                            <div class="oracion">
                                <p class="oracion-nombre">INVOCACIÓN AL ESPÍRITU SANTO</p>
                                <p>Ven, Espíritu Santo,<br>
                                    llena los corazones de tus fieles<br>
                                    y enciende en ellos el fuego de tu amor.<br>
                                    Envía tu Espíritu y todo será creado<br>
                                    y renovarás la faz de la tierra.<br>
                                    Oh Dios, que has instruido los corazones de tus fieles<br>
                                    con la luz del Espíritu Santo,<br>
                                    concédenos que sintamos rectamente con el mismo Espíritu<br>
                                    y gocemos siempre de tu divino consuelo.<br>
                                    Por Jesucristo nuestro Señor. Amén.</p>
                            </div>
                            <div class="oracion">
                                <p class="oracion-nombre">PADRE NUESTRO</p>
                                <p>Padre nuestro,<br>
                                    que estás en el cielo,<br>
                                    santificado sea tu nombre;<br>
                                    venga a nosotros tu reino;<br>
                                    hágase tu voluntad<br>
                                    en la tierra como en el cielo.<br>
                                    Danos hoy nuestro pan de cada día;<br>
                                    perdona nuestras ofensas,<br>
                                    como también nosotros perdonamos<br>
                                    a los que nos ofenden;<br>
                                    no nos dejes caer en la tentación<br>
                                    y líbranos del mal. Amén.</p>
                            </div>
                        </div>

                        <div class="oracion oracion-credo">
                            <p class="oracion-nombre">EL CREDO</p>
                            <p>Creo en Dios, Padre todopoderoso, creador del cielo y de la tierra. Creo en Jesucristo, su único Hijo, nuestro Señor, que fue concebido por obra y gracia del Espíritu Santo;<br>
                                nació de Santa María Virgen, padeció bajo el poder de Poncio Pilato;<br>
                                fue crucificado, muerto y sepultado;<br>
                                descendió a los infiernos; al tercer día resucitó entre los muertos; subió a los cielos y está sentado a la derecha de Dios, Padre todopoderoso.<br>
                                Desde allí ha de venir a juzgar a los vivos y a los muertos. Creo en el Espíritu Santo, en la santa Iglesia católica, la comunión de los santos, el perdón de los pecados, la resurrección de los muertos y la vida eterna. Amén.</p>
                        </div>
                    </article>
                @endforeach
            </section>
        @endforeach
    @endforeach

@include('partials.footerImpresion')
