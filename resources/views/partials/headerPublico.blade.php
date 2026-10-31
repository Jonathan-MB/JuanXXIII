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

        <nav class="header-publico-botones" aria-label="Accesos">
            <a class="boton-amarillo" href="{{ route('inscripcion') }}">Inscribirme al próximo retiro</a>
            <a class="boton-borde-claro" href="{{ route('login') }}">Iniciar sesión</a>
        </nav>
    </header>
