@php
    use App\Models\Rol;

    $usuario      = auth()->user();
    $verTodo      = $usuario->tieneRol(Rol::ADMINISTRACION, Rol::SECRETARIA);
    $verPagos     = $verTodo || $usuario->tieneRol(Rol::TESORERIA);
    $verDirectivo = $usuario->tieneRol(Rol::ADMINISTRACION, Rol::DIRECTIVOS);
@endphp
</head>

<body class="cuerpo-panel">

    <header class="header-panel">
        <a class="header-panel-logo" href="{{ route('panel') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
            <span>Juan XXIII</span>
        </a>

        {{-- Buscador general: desde cualquier página --}}
        @unless (request()->routeIs('buscar'))
            <form class="buscador-cabecera" method="GET" action="{{ route('buscar') }}" role="search">
                <input type="search" name="q" placeholder="Buscar hojas, letreros, personas…" aria-label="Buscar en toda la aplicación" autocomplete="off">
                <button type="submit" aria-label="Buscar">Buscar</button>
            </form>
        @endunless

        <div class="header-panel-usuario" id="contenedor-usuario">
            <div class="usuario-datos">
                <p class="usuario-nombre">{{ $usuario->nombre }}</p>
                <p class="usuario-rol">{{ $usuario->rol?->nombre }}{{ $usuario->equipo ? ' · ' . $usuario->equipo->nombre : '' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="boton-salir">Cerrar sesión</button>
            </form>
        </div>
    </header>

    <div class="estructura-panel">

        {{-- --------------------MENÚ SEGÚN EL ROL------------------- --}}
        <nav class="menu-panel" aria-label="Menú principal">
            <a class="menu-enlace {{ request()->routeIs('panel') ? 'menu-activo' : '' }}" href="{{ route('panel') }}">Inicio</a>
            <a class="menu-enlace menu-buscar {{ request()->routeIs('buscar') ? 'menu-activo' : '' }}" href="{{ route('buscar') }}">Buscar</a>

            @if ($verPagos)
                <p class="menu-grupo">Retiro actual</p>
                <a class="menu-enlace {{ request()->routeIs('inscritos.*') ? 'menu-activo' : '' }}" href="{{ route('inscritos.index') }}">Inscritos</a>
                <a class="menu-enlace {{ request()->routeIs('pagos.*') ? 'menu-activo' : '' }}" href="{{ route('pagos.index') }}">Pagos</a>
            @endif

            @if ($verTodo)
                <a class="menu-enlace {{ request()->routeIs('llegada.*') ? 'menu-activo' : '' }}" href="{{ route('llegada.index') }}">Llegada</a>
                <a class="menu-enlace {{ request()->routeIs('mesas.*') ? 'menu-activo' : '' }}" href="{{ route('mesas.index') }}">Mesas</a>
                <a class="menu-enlace {{ request()->routeIs('impresiones.*') ? 'menu-activo' : '' }}" href="{{ route('impresiones.index') }}">Impresiones</a>
                <a class="menu-enlace {{ request()->routeIs('desarrollo.*') ? 'menu-activo' : '' }}" href="{{ route('desarrollo.index') }}">Desarrollo del retiro</a>
            @endif

            <p class="menu-grupo">Horarios</p>
            <a class="menu-enlace {{ request()->routeIs('santisimo.*') ? 'menu-activo' : '' }}" href="{{ route('santisimo.index') }}">Santísimo</a>

            @if ($verTodo)
                <p class="menu-grupo">Archivos</p>
                @foreach (\App\Models\Archivo::CATEGORIAS as $claveMenu => $etiquetaMenu)
                    {{-- Letreros lleva a la galería con vista previa; sus archivos originales se abren desde allí --}}
                    @if ($claveMenu === 'letreros')
                        <a class="menu-enlace {{ request()->routeIs('letreros.*') || (request()->routeIs('archivos.*') && request('categoria') === 'letreros') ? 'menu-activo' : '' }}" href="{{ route('letreros.index') }}">{{ $etiquetaMenu }}</a>
                    @else
                        <a class="menu-enlace {{ request()->routeIs('archivos.*') && request('categoria', 'documentos') === $claveMenu ? 'menu-activo' : '' }}" href="{{ route('archivos.index', ['categoria' => $claveMenu]) }}">{{ $etiquetaMenu }}</a>
                    @endif
                @endforeach
            @endif

            <p class="menu-grupo">General</p>
            @if ($verTodo)
                <a class="menu-enlace {{ request()->routeIs('retiros.*') ? 'menu-activo' : '' }}" href="{{ route('retiros.index') }}">Retiros</a>
                <a class="menu-enlace {{ request()->routeIs('perseverancias.*') ? 'menu-activo' : '' }}" href="{{ route('perseverancias.index') }}">Perseverancias</a>
                <a class="menu-enlace {{ request()->routeIs('historial.*') ? 'menu-activo' : '' }}" href="{{ route('historial.index') }}">Historial</a>
                <a class="menu-enlace {{ request()->routeIs('directoriosAnteriores') ? 'menu-activo' : '' }}" href="{{ route('directoriosAnteriores') }}">Directorios anteriores</a>
                <a class="menu-enlace {{ request()->routeIs('ajustes.*') ? 'menu-activo' : '' }}" href="{{ route('ajustes.index') }}">Ajustes</a>
            @endif
            <a class="menu-enlace {{ request()->routeIs('inventario.*') ? 'menu-activo' : '' }}" href="{{ route('inventario.index') }}">Inventario</a>

            @if ($verDirectivo)
                <p class="menu-grupo">Directivos</p>
                <a class="menu-enlace {{ request()->routeIs('directivos.*') ? 'menu-activo' : '' }}" href="{{ route('directivos.index') }}">Inscritos del retiro</a>
                {{-- Administración ya lo tiene arriba, en Retiro actual --}}
                @unless ($verTodo)
                    <a class="menu-enlace {{ request()->routeIs('desarrollo.*') ? 'menu-activo' : '' }}" href="{{ route('desarrollo.index') }}">Desarrollo del retiro</a>
                @endunless
            @endif

            @if ($usuario->tieneRol(Rol::ADMINISTRACION))
                <p class="menu-grupo">Administración</p>
                <a class="menu-enlace {{ request()->routeIs('usuarios.*') ? 'menu-activo' : '' }}" href="{{ route('usuarios.index') }}">Cuentas</a>
            @endif
        </nav>

        <main class="contenedor-general">
