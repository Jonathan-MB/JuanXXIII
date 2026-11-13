</head>

<body class="cuerpo-publico">

    <header class="header-publico">
        <a class="header-publico-logo" href="{{ route('inicio') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
            <span class="header-publico-nombre">
                <small>Movimiento de Retiros Parroquiales</small>
                Juan XXIII
            </span>
        </a>

        @php
            $enlaceRosario = \App\Models\Ajuste::valor(\App\Models\Ajuste::ROSARIO_ENLACE);
        @endphp
        <nav class="header-publico-botones" aria-label="Accesos">
            @if ($enlaceRosario)
                <a class="boton-rosario" href="{{ $enlaceRosario }}" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="4" r="1.6"/><circle cx="17.2" cy="6.2" r="1.6"/><circle cx="19.4" cy="11.2" r="1.6"/><circle cx="6.8" cy="6.2" r="1.6"/><circle cx="4.6" cy="11.2" r="1.6"/><path d="M12 14v8M9 17h6"/></svg>
                    Rosario en vivo
                </a>
            @endif
            <a class="boton-amarillo" href="{{ route('inscripcion') }}">Inscribirme al próximo retiro</a>
            <a class="boton-borde-claro" href="{{ route('login') }}">Iniciar sesión</a>
        </nav>
    </header>
