@php
    //  "R. 90 M – 91 H", como en los letreros de siempre
    $retiroCorto = $retiro ? 'R. ' . $retiro->numero_mujeres . ' M – ' . $retiro->numero_hombres . ' H' : '';

    //  Un celular se lee de a 3, 3 y 4 cifras: "315 572 4732"
    $digitos      = preg_replace('/\D/', '', (string) $cuenta?->numero);
    $numeroCuenta = strlen($digitos) === 10
        ? substr($digitos, 0, 3) . ' ' . substr($digitos, 3, 3) . ' ' . substr($digitos, 6)
        : trim(chunk_split($digitos, 3, ' '));
@endphp
@include('partials.headImpresion')

    @switch($clave)

        {{-- --------------------SILENCIO------------------- --}}
        @case('silencio')
            <section class="pagina letrero-pared">
                @include('letreros.icono', ['icono' => 'silencio', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="titulo-letrero titulo-gigante">Silencio</p>
                    <p class="subtitulo-letrero">Estamos en oración</p>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------SANTÍSIMO------------------- --}}
        @case('santisimo')
            <section class="pagina letrero-pared letrero-dorado">
                @include('letreros.icono', ['icono' => 'custodia', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="antetitulo-letrero">Adoración al</p>
                    <p class="titulo-letrero">Santísimo Sacramento</p>
                    <p class="subtitulo-letrero">Entra en silencio · Jesús está aquí</p>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------AYUDAS DE DIOS------------------- --}}
        @case('ayudasDeDios')
            <section class="pagina letrero-pared">
                @include('letreros.icono', ['icono' => 'sobre', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="titulo-letrero">Ayudas de Dios</p>
                    <p class="retiro-letrero">{{ $retiroCorto }}</p>
                    <div class="recuadros-letrero">
                        <div class="recuadro-letrero">
                            <p class="titulo-recuadro">Horario de recepción</p>
                            <p class="dato-grande">Sábado</p>
                            <p class="dato-grande">2:00 p. m. – 4:00 p. m.</p>
                        </div>
                        <div class="recuadro-letrero recuadro-aviso">
                            <p class="titulo-recuadro">No se reciben</p>
                            {{-- Una viñeta por cada carta que no se recibe: sin ella, el salto de línea confundía --}}
                            <ul class="lista-no-recibe">
                                <li>Cartas escritas por personas que <strong>no han hecho el retiro</strong></li>
                                <li>Cartas hechas en <strong>computador</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------AYUDAS DE DIOS: LA CAJA------------------- --}}
        {{-- Para la caja donde se dejan las cartas: cada carta marcada con la mesa y la silla del retirista --}}
        @case('ayudasCaja')
            <section class="pagina letrero-pared">
                @include('letreros.icono', ['icono' => 'sobre', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="antetitulo-letrero">Caja de</p>
                    <p class="titulo-letrero titulo-gigante">Ayudas de Dios</p>
                    <p class="retiro-letrero">{{ $retiroCorto }}</p>
                    <p class="aviso-puesto aviso-puesto-grande"><span>Por favor marca cada carta</span><span>con la <strong>mesa</strong> y la <strong>silla</strong></span><span>del retirista</span><span class="ejemplo-puesto">M 1 – S 1</span></p>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------NEQUI: una hoja por equipo------------------- --}}
        @case('nequi')
            @foreach ($equipos as $equipo)
                <section class="pagina letrero-pared">
                    @include('letreros.icono', ['icono' => 'celular', 'clase' => 'icono-grande'])
                    <div class="texto-letrero">
                        <p class="antetitulo-letrero">{{ $equipo }} · Paga por</p>
                        <p class="titulo-letrero titulo-nequi">Nequi</p>
                        @if ($cuenta)
                            <p class="numero-cuenta">{{ $numeroCuenta }}</p>
                            <p class="subtitulo-letrero">A nombre de {{ $cuenta->titular }}</p>
                        @else
                            <p class="numero-cuenta linea-escribir">&nbsp;</p>
                            <p class="subtitulo-letrero">A nombre de ______________________</p>
                        @endif
                        <p class="aviso-descripcion">Por favor, en la descripción del pago escribe en qué equipo compraste: <strong>{{ $equipo }}</strong></p>
                    </div>
                    @include('letreros.pie')
                </section>
            @endforeach
            @break

        {{-- --------------------NO CEPILLARSE------------------- --}}
        @case('noCepillarse')
            <section class="pagina letrero-pared letrero-prohibido">
                @include('letreros.icono', ['icono' => 'cepillo', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="titulo-letrero">No cepillarse aquí</p>
                    <p class="subtitulo-letrero">Por favor, usa los lavamanos de los baños</p>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------RESIDUOS DE COMIDA------------------- --}}
        @case('residuos')
            <section class="pagina letrero-pared">
                @include('letreros.icono', ['icono' => 'caneca', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="titulo-letrero">Residuos de comida</p>
                    <p class="subtitulo-letrero">Solo restos de comida · Nada de plásticos ni servilletas</p>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------SOLO HOMBRES / SOLO MUJERES y BEBIDAS: una hoja por grupo------------------- --}}
        @case('soloGrupo')
        @case('bebidas')
            @foreach (['Hombres' => ['hombre', '#1c2f5e'], 'Mujeres' => ['mujer', '#8a2346']] as $nombreGrupo => [$figura, $colorGrupo])
                <section class="pagina letrero-pared letrero-{{ mb_strtolower($nombreGrupo) }}">
                    @include('letreros.icono', ['icono' => $clave === 'bebidas' ? 'bebida' : $figura, 'color' => $colorGrupo, 'clase' => 'icono-grande'])
                    <div class="texto-letrero">
                        {{-- El grupo es lo que más importa: va grande; "Solo" o "Bebidas para", pequeño encima --}}
                        <p class="antetitulo-letrero antetitulo-grupo">{{ $clave === 'bebidas' ? 'Bebidas para' : 'Solo' }}</p>
                        <p class="titulo-letrero titulo-gigante">{{ $nombreGrupo }}</p>
                        @if ($clave === 'bebidas')
                            {{-- Para saber de quién es cada vaso --}}
                            <p class="aviso-puesto"><span>Por favor coloca la <strong>mesa</strong></span><span>y la <strong>silla</strong> de tu invitado</span><span class="ejemplo-puesto">M 1 – S 1</span></p>
                        @endif
                    </div>
                    @include('letreros.pie')
                </section>
            @endforeach
            @break

        {{-- --------------------BASURA------------------- --}}
        @case('basura')
            <section class="pagina letrero-pared">
                @include('letreros.icono', ['icono' => 'basura', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="titulo-letrero titulo-gigante">Basura</p>
                    <p class="subtitulo-letrero">Papeles, plásticos y empaques</p>
                    <p class="nota-letrero">Mantengamos limpia la casa · ¡Gracias!</p>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------NO PAPELES AL INODORO------------------- --}}
        @case('noPapeles')
            <section class="pagina letrero-pared letrero-prohibido">
                @include('letreros.icono', ['icono' => 'inodoroPapel', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="titulo-letrero titulo-azul"><span class="palabra-roja">No</span> arroje papeles al inodoro</p>
                    <p class="subtitulo-letrero">Ni toallitas, tampones o toallas higiénicas. Use la papelera.</p>
                    <p class="nota-letrero">Mantengamos limpio el baño · ¡Gracias!</p>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------BAÑO FUERA DE SERVICIO------------------- --}}
        @case('banoFueraServicio')
            <section class="pagina letrero-pared letrero-prohibido">
                @include('letreros.icono', ['icono' => 'inodoro', 'clase' => 'icono-grande'])
                <div class="texto-letrero">
                    <p class="antetitulo-letrero">Baño</p>
                    <p class="titulo-letrero">Fuera de servicio</p>
                    <p class="subtitulo-letrero">Disculpa las molestias · Usa otro baño</p>
                </div>
                @include('letreros.pie')
            </section>
            @break

        {{-- --------------------ABRE LA CAJA: cinco hojas------------------- --}}
        @case('abreLaCaja')
            @foreach (['Abre la caja', 'Paso 1', 'Paso 2', 'Paso 3', 'Lee por favor'] as $texto)
                <section class="pagina letrero-pared letrero-centrado">
                    @if ($loop->first)
                        @include('letreros.icono', ['icono' => 'caja', 'clase' => 'icono-grande'])
                    @endif
                    <div class="texto-letrero">
                        <p class="titulo-letrero titulo-gigante">{{ $texto }}</p>
                    </div>
                    @include('letreros.pie')
                </section>
            @endforeach
            @break

        {{-- --------------------ETIQUETAS DE MESA: hoja vertical por mesa------------------- --}}
        @case('etiquetasMesa')
            @foreach ($mesas as $sexo => $numeros)
                @foreach ($numeros as $numero)
                    <section class="pagina hoja-etiquetas">
                        @for ($i = 0; $i < 24; $i++)
                            <div class="etiqueta-mesa {{ $sexo === 'Femenino' ? 'etiqueta-mujeres' : 'etiqueta-hombres' }}">
                                <p class="etiqueta-numero">Mesa {{ $numero }}</p>
                                <p class="etiqueta-grupo">{{ $retiro ? 'R. ' . $retiro->numeroPara($sexo) . ' ' : '' }}{{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }}</p>
                            </div>
                        @endfor
                    </section>
                @endforeach
            @endforeach
            @break
    @endswitch

<script src="{{ recurso('js/letrerosHoja.js') }}"></script>
@include('partials.footerImpresion')
