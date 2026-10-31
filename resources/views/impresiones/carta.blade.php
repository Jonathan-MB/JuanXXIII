@php
    $fechaCarta = $retiro->fecha_fin->locale('es')->translatedFormat('j \d\e F \d\e Y');
@endphp
@include('partials.headImpresion')

    @forelse ($inscritos as $inscrito)
        @php
            $persona = $inscrito->persona;
            $trato   = $persona->sexo === 'Femenino' ? 'a la hermana' : 'al hermano';
        @endphp
        <article class="pagina carta">
            @include('partials.membrete')

            <div class="carta-destinatario">
                <p>PRESBÍTERO</p>
                <p class="linea-mano"></p>
                <p>{{ $retiro->ciudad }}, {{ $fechaCarta }}</p>
            </div>

            <p>Reciba cordial saludo de parte del MOVIMIENTO DE RETIROS PARROQUIALES JUAN XXIII, el cual es una asociación de laicos en pro de la evangelización que tiene como misión realizar RETIROS DE CONVERSIÓN (Kerigmáticos), que se enfoca en traer a las personas que están alejadas de nuestra Iglesia católica, aquellos que por su problemática viven pensando que el Evangelio no puede ser predicado para ellos y necesitan de alguien que los evangelice y les anuncie a Cristo. Este movimiento tiene un carisma propio, un don que el Señor le ha confiado, una espiritualidad de auténtico servicio enmarcada en el amor, entrega y sacrificio, expresada en una opción de vida. “CON CRISTO TODO Y SIN CRISTO NADA”.</p>

            <p>Acabamos de celebrar el retiro {{ $retiro->numero_mujeres }} de mujeres y {{ $retiro->numero_hombres }} de hombres los días {{ $retiro->dias_texto }}, en donde queremos presentarle y colocar a su disposición {{ $trato }} <strong>{{ mb_strtoupper($persona->nombre_completo) }}</strong> y pedirle su acompañamiento en su proceso de formación y crecimiento espiritual en algunos de los espacios que usted disponga.</p>

            <p>El movimiento también cuenta con estos espacios de formación y crecimiento que colocamos en disponibilidad de los retiristas. Estamos a su disposición en lo que usted disponga en su parroquia.</p>

            @if ($proximo)
                <p>Nuestro próximo retiro de conversión simultáneo será los días {{ $proximo->dias_texto }} en el {{ mb_strtoupper($proximo->lugar) }} en la ciudad de {{ $proximo->ciudad }}.</p>
            @else
                <p class="parrafo-con-espacios">Nuestro próximo retiro de conversión simultáneo será los días <span class="espacio-mano"></span> en <span class="espacio-mano espacio-largo"></span>.</p>
            @endif

            <p>Si la parroquia requiere cupos para vivir esta experiencia u otra información, no dude en comunicarse con nosotros.</p>

            <p class="carta-despedida">Cordialmente,</p>

            <div class="firmas">
                @foreach ($firmantes as $firmante)
                    <div class="firma">
                        <p class="firma-nombre">{{ mb_strtoupper($firmante->nombre) }}</p>
                        <p>{{ $firmante->cargo }}</p>
                        @if ($firmante->celular)
                            <p>Cel: {{ $firmante->celular }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay cartas para imprimir: nadie de este grupo tiene marcada la llegada. Las cartas son solo para quienes llegaron al retiro.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
