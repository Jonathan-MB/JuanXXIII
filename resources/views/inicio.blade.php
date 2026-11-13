@include('partials.head')

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap">
<link rel="stylesheet" href="{{ recurso('css/inicio.css') }}">
<title>Retiros Juan XXIII</title>

@include('partials.headerPublico')

    {{-- --------------------PORTADA------------------- --}}
    <section class="portada">
        <div class="portada-contenido">
            <p class="portada-movimiento">Arquidiócesis de Cali</p>
            <h1 class="portada-titulo">
                <span class="portada-titulo-amarillo">Retiro</span>
                Juan XXIII
            </h1>
            <p class="portada-subtitulo">Para hombres y mujeres</p>

            @if ($retiro)
                <p class="portada-fechas">{{ $retiro->dias_texto }}</p>
                <p class="portada-lugar">
                    <svg class="icono-lugar" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.6"/></svg>
                    {{ $retiro->lugar }} · {{ $retiro->ciudad }}
                </p>
                <a class="boton-amarillo boton-grande" href="{{ route('inscripcion') }}">Inscribirme</a>
            @else
                <p class="portada-fechas">Muy pronto anunciaremos el próximo retiro</p>
                <p class="portada-lugar">Mientras tanto, te esperamos en las perseverancias.</p>
            @endif

            <p class="portada-cita">“El Señor es mi pastor, nada me falta.” <span>(Sal 23)</span></p>
        </div>

        <div class="portada-sello" aria-hidden="true">
            <div class="sello-rayos"></div>
            <img src="{{ asset('img/logo.png') }}" alt="">
        </div>
    </section>

    @if ($retiro)
        {{-- --------------------DATOS DEL RETIRO------------------- --}}
        <section class="franja-datos">
            <div class="dato-retiro">
                <div class="dato-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M9 3v4M15 3v4"/></svg>
                </div>
                <div>
                    <p class="dato-titulo">Fechas</p>
                    <p class="dato-texto">{{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</p>
                    <p class="dato-detalle">Viernes desde las {{ \Carbon\Carbon::parse($retiro->hora_inicio)->format('g:i a') }} hasta el domingo a las {{ \Carbon\Carbon::parse($retiro->hora_fin)->format('g:i a') }}</p>
                </div>
            </div>

            <div class="dato-retiro">
                <div class="dato-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M5 20V9l7-5 7 5v11z"/><path d="M10 20v-6h4v6"/></svg>
                </div>
                <div>
                    <p class="dato-titulo">Ofrenda</p>
                    <p class="dato-texto">{{ $retiro->ofrenda_texto }}</p>
                    @if ($retiro->que_incluye)
                        <p class="dato-detalle">{{ $retiro->que_incluye }}</p>
                    @endif
                </div>
            </div>

            <div class="dato-retiro">
                <div class="dato-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 7v10M9.5 9.5c0-1 1-1.6 2.5-1.6s2.5.7 2.5 1.7-1 1.4-2.5 1.7-2.5.8-2.5 1.8 1 1.8 2.5 1.8 2.5-.6 2.5-1.6"/></svg>
                </div>
                <div>
                    <p class="dato-titulo">Reserva tu cupo</p>
                    <p class="dato-texto">Con el {{ $retiro->porcentaje_reserva }}% de la ofrenda</p>
                    @foreach ($retiro->cuentasPago as $cuenta)
                        <p class="dato-detalle">{{ $cuenta->entidad }} {{ $cuenta->numero }} ({{ $cuenta->titular }})</p>
                    @endforeach
                    @if ($retiro->fecha_limite_pago)
                        <p class="dato-detalle">Fecha límite para completar el pago: <strong>{{ $retiro->fecha_limite_pago->locale('es')->translatedFormat('j \d\e F') }}</strong></p>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- --------------------ANTES DE INSCRIBIRTE------------------- --}}
    <section class="seccion-requisitos">
        <h2 class="titulo-seccion">Antes de inscribirte</h2>
        <ul class="lista-requisitos">
            <li>El retiro es para mayores de 16 años.</li>
            <li>No lo pueden vivir mujeres en embarazo ni personas con enfermedades de manejo psiquiátrico.</li>
            <li>No lleves celular: no lo vas a necesitar.</li>
            <li>Si cancelas después de reservar, el dinero no se devuelve por los gastos del retiro.</li>
        </ul>
    </section>

    @if ($rosario)
        {{-- --------------------ROSARIO------------------- --}}
        <section class="seccion-rosario" id="rosario">
            <div class="tarjeta-rosario">
                <img class="imagen-rosario" src="{{ asset('img/rosario.svg') }}" alt="La Virgen María con un rosario" width="400" height="460" loading="lazy">

                <div class="texto-rosario">
                    <p class="rosario-invitacion">Te invitamos a continuar este <span>hermoso caminar</span></p>
                    <p class="rosario-cita">Unidos en oración, tenemos una cita con</p>
                    <h2 class="rosario-titulo">Mamita María</h2>
                    <p class="rosario-subtitulo">Santo Rosario</p>

                    <div class="rosario-datos">
                        @if ($rosario->dias || $rosario->hora)
                            <div class="rosario-dato">
                                <p class="rosario-dato-titulo">Todos los días</p>
                                <p class="rosario-dias">{{ $rosario->dias }}</p>
                                <p class="rosario-hora">{{ $rosario->hora }}</p>
                            </div>
                        @endif
                        @if ($rosario->pedidos)
                            <div class="rosario-dato">
                                <p class="rosario-dato-titulo">Pedidos de oración</p>
                                <a class="rosario-whatsapp" href="https://wa.me/57{{ $rosario->pedidos }}" target="_blank" rel="noopener">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20l1.2-3.6A8 8 0 1 1 8 19z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1.2-1.4-1.9-.9-.9.8a4 4 0 0 1-2.4-2.4l.8-.9-.9-1.9z"/></svg>
                                    {{ $rosario->pedidosTexto }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <a class="boton-amarillo boton-grande" href="{{ $rosario->enlace }}" target="_blank" rel="noopener">Unirme al rosario</a>
                    <p class="rosario-nota">Por Google Meet, en el enlace de la Liturgia de las Horas.</p>
                </div>
            </div>
        </section>
    @endif

    {{-- --------------------LEMA------------------- --}}
    <section class="franja-lema">
        <p><span class="lema-verde">Con Cristo todo</span> sin Cristo nada</p>
    </section>

    {{-- --------------------PIE------------------- --}}
    <footer class="pie-publico">
        <img src="{{ asset('img/logo.png') }}" alt="">
        <div>
            <p class="pie-nombre">Movimiento de Retiros Parroquiales Juan XXIII</p>
            <p>Arquidiócesis de Cali · Colombia</p>
        </div>

        @if ($retiro && $retiro->contactosInformes->isNotEmpty())
            <div class="pie-informes">
                <p class="pie-informes-titulo">Informes</p>
                @foreach ($retiro->contactosInformes as $contacto)
                    <p>{{ $contacto->nombre }} · <strong>{{ $contacto->celular }}</strong></p>
                @endforeach
            </div>
        @endif
    </footer>

@include('partials.footer')
