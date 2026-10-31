@include('partials.head')

<link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion.css') }}">
<title>Inscripción recibida · Retiros Juan XXIII</title>

@include('partials.headerPublico')

    <section class="tarjeta-recibida">
        <div class="icono-recibida" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
        </div>

        <h1 class="titulo-seccion">¡Gracias, {{ $recibida['nombre'] }}!</h1>
        <p class="texto-seccion">Hemos recibido tu inscripción.</p>

        <p class="retiro-recibido">{{ $recibida['retiro'] }} · {{ $recibida['fechas'] }}</p>

        @unless ($recibida['conComprobante'])
            <p class="recordatorio-soporte">
                Recuerda: tu cupo se reserva cuando envíes el soporte del pago
                @if ($retiro && $retiro->cuentasPago->isNotEmpty())
                    al número de la cuenta donde pagaste ({{ $retiro->cuentasPago->pluck('numero')->join(' o ') }}).
                @else
                    a la persona de informes.
                @endif
            </p>
        @endunless

        @if ($retiro && $retiro->contactosInformes->isNotEmpty())
            <p class="ayuda-campo">
                ¿Dudas? Escribe a
                {{ $retiro->contactosInformes->map(fn ($contacto) => $contacto->nombre . ' (' . $contacto->celular . ')')->join(', ', ' o ') }}.
            </p>
        @endif

        <p class="lema-recibida">¡Dios te bendiga!</p>
        <a class="boton-azul" href="{{ route('inicio') }}">Volver al inicio</a>
    </section>

@include('partials.footer')
