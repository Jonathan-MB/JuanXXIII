@php
    $calificaciones = ['Presentación', 'Dinamismo', 'Lenguaje', 'Conocimiento de la materia', 'Dominio de la charla'];
@endphp
@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $grupo)
        @php
            $esMujer = $sexo === 'Femenino';
        @endphp
        {{-- Una hoja por charla: lo general es igual en todas y los temas cambian --}}
        @foreach ($grupo['charlas'] as $charla)
            <section class="pagina evaluacion-charla">
                @include('partials.membrete', ['subtitulo' => $titulo . ' · ' . ($esMujer ? 'Mujeres' : 'Hombres')])

                <p class="datos-informe">
                    <span><strong>Retiro N.º</strong> {{ $retiro->numeroPara($sexo) }}</span>
                    <span><strong>Fecha:</strong> {{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</span>
                </p>
                <h2 class="nombre-charla">Conferencia: {{ $charla['nombre'] }}</h2>

                <div class="preguntas-charla">
                    <p><strong>Conferencista:</strong> <span class="hueco hueco-nombre">{{ $charla['charlista'] }}</span> <strong>Diócesis:</strong> <span class="hueco"></span></p>
                    <p>Comenzó a: <span class="hueco hueco-corto"></span> a. m. <span class="casilla"></span> p. m. <span class="casilla"></span> · Terminó a: <span class="hueco hueco-corto"></span> a. m. <span class="casilla"></span> p. m. <span class="casilla"></span> · Duración: <span class="hueco hueco-corto"></span></p>
                    <p>Bienvenida / introducción: <span class="hueco"></span> · Oración al Espíritu Santo @include('impresiones.informes.siNo')</p>
                    <p>Estilo de presentación: <span class="hueco"></span> · ¿Usó el estilo correcto? @include('impresiones.informes.siNo')</p>
                    <p>¿Lleva testimonio? @include('impresiones.informes.siNo') · ¿Fue dado? @include('impresiones.informes.siNo') · Tiempo utilizado: <span class="hueco hueco-corto"></span></p>
                    <p>¿Lleva reto / invitación? @include('impresiones.informes.siNo') · ¿Fue hecho? @include('impresiones.informes.siNo') · ¿De manera apropiada? @include('impresiones.informes.siNo')</p>
                    <p>¿Previsto? @include('impresiones.informes.siNo') · ¿Fue hecho? @include('impresiones.informes.siNo') · ¿De manera apropiada? @include('impresiones.informes.siNo')</p>
                    <p><strong>Doctrina:</strong> ¿Fue enseñada? @include('impresiones.informes.siNo') · Si no, ¿en qué falló? <span class="hueco hueco-largo"></span></p>
                    <p><strong>Tiempo:</strong> ¿Lo utilizó de forma apropiada? @include('impresiones.informes.siNo') · Si no, explique: <span class="hueco hueco-largo"></span></p>
                </div>

                @if ($charla['temas'])
                    <h3 class="titulo-informe">Temas importantes de la charla</h3>
                    <ul class="temas-charla">
                        @foreach ($charla['temas'] as $renglon)
                            <li>
                                @foreach ($renglon as $tema)
                                    {{-- Un tema que termina en ":" es un subtítulo, sin casillas --}}
                                    @if (str_ends_with($tema, ':'))
                                        <strong>{{ $tema }}</strong>
                                    @else
                                        <span class="tema">{{ $tema }} @include('impresiones.informes.siNo')</span>
                                    @endif
                                @endforeach
                            </li>
                        @endforeach
                    </ul>
                @else
                    <h3 class="titulo-informe">Temas importantes de la charla</h3>
                    @include('impresiones.informes.renglones', ['cantidad' => 6])
                @endif

                <div class="preguntas-charla">
                    <p>¿Habló de los temas importantes? @include('impresiones.informes.siNo') · ¿De manera apropiada? @include('impresiones.informes.siNo') · Si no, explique:</p>
                    @include('impresiones.informes.renglones', ['cantidad' => 2])
                    <p>Despedida: <span class="hueco hueco-largo"></span></p>
                </div>

                <h3 class="titulo-informe">Comentarios finales: ¿cómo califica al conferencista?</h3>
                <table class="tabla-impresion tabla-informe">
                    <thead>
                        <tr>
                            @foreach ($calificaciones as $aspecto)
                                <th>{{ $aspecto }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach ($calificaciones as $aspecto)
                                <td></td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>

                <p class="preguntas-charla"><strong>Observaciones:</strong></p>
                @include('impresiones.informes.renglones', ['cantidad' => 2])

                <p class="pie-evaluacion">
                    Firma: <span class="hueco hueco-nombre"></span>
                    Retiro: <span class="hueco hueco-corto"></span>
                    Mesa: <span class="hueco hueco-corto"></span>
                    Silla: <span class="hueco hueco-corto"></span>
                </p>
            </section>
        @endforeach
    @endforeach

@include('partials.footerImpresion')
