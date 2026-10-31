@include('partials.head')

<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<title>Iniciar sesión · Retiros Juan XXIII</title>

</head>

<body class="cuerpo-login">

    <div class="contenedor-login">
        <a class="login-logo" href="{{ route('inicio') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
        </a>
        <h1 class="login-titulo">Iniciar sesión</h1>
        <p class="login-texto">Solo para servidores con cuenta activa.</p>

        <form class="tarjeta-login" action="{{ route('web.login') }}" method="post">
            @csrf

            @if ($errors->has('usuario'))
                <p class="login-error">{{ $errors->first('usuario') }}</p>
            @endif
            @if (session('error'))
                <p class="login-error">{{ session('error') }}</p>
            @endif

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="usuario">Usuario</label>
                <input type="text" name="usuario" id="usuario" maxlength="45" value="{{ old('usuario') }}" autocomplete="username" autocapitalize="none" required>
            </div>

            <div class="elemento-formulario">
                <label class="titulo-elemento" for="password">Contraseña</label>
                <input type="password" name="password" id="password" autocomplete="current-password" required>
            </div>

            <label class="casilla-recordarme">
                <input type="checkbox" name="recordarme" value="1" {{ old('recordarme') ? 'checked' : '' }}>
                <span>Recordarme en este dispositivo por 3 días</span>
            </label>

            <button type="submit" class="boton-azul boton-ancho">Entrar</button>
        </form>

        <a class="login-volver" href="{{ route('inicio') }}">Volver al inicio</a>
    </div>

</body>

</html>
