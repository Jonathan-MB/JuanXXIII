@php
    $editando = $cuenta->exists;
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/usuarios.css') }}">
<title>{{ $editando ? 'Editar cuenta' : 'Nueva cuenta' }} · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">{{ $editando ? 'Editar cuenta de ' . $cuenta->nombre : 'Nueva cuenta' }}</h1>
    @include('partials.mensaje')

    <form class="formulario-cuenta" method="POST" action="{{ $editando ? route('usuarios.update', $cuenta) : route('usuarios.store') }}" id="formulario-cuenta">
        @csrf
        @if ($editando)
            @method('PUT')
        @endif

        {{-- --------------------DATOS------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Datos de la cuenta</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="nombre">Nombre de la persona</label>
                    <input type="text" name="nombre" id="nombre" maxlength="60" value="{{ old('nombre', $cuenta->nombre) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="usuario">Usuario para entrar</label>
                    <input type="text" name="usuario" id="usuario" maxlength="45" autocomplete="off" autocapitalize="none" value="{{ old('usuario', $cuenta->usuario) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="correo">Correo <small>(opcional)</small></label>
                    <input type="email" name="correo" id="correo" maxlength="100" value="{{ old('correo', $cuenta->correo) }}">
                </div>
            </div>
        </fieldset>

        {{-- --------------------ROL Y EQUIPO------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Rol y equipo</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="rolId">Rol</label>
                    <select name="rolId" id="rolId" required>
                        <option value="">Elige un rol</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" {{ (int) old('rolId', $cuenta->rol_id) === $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="equipoId">Equipo</label>
                    <select name="equipoId" id="equipoId">
                        <option value="">Sin equipo</option>
                        @foreach ($equipos as $equipo)
                            <option value="{{ $equipo->id }}" {{ (int) old('equipoId', $cuenta->equipo_id) === $equipo->id ? 'selected' : '' }}>{{ $equipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="texto-ayuda">Administración y Secretaría ven todo. Directivos y los demás equipos solo ven los horarios y el inventario de su equipo. Solo Administración puede crear y editar cuentas.</p>
        </fieldset>

        {{-- --------------------CONTRASEÑA------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>{{ $editando ? 'Cambiar contraseña' : 'Contraseña' }}</legend>
            @if ($editando)
                <p class="texto-ayuda texto-ayuda-arriba">Déjalas en blanco para no cambiar la contraseña.</p>
            @endif
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="contrasena">{{ $editando ? 'Nueva contraseña' : 'Contraseña' }}</label>
                    <input type="password" name="contrasena" id="contrasena" minlength="8" autocomplete="new-password" {{ $editando ? '' : 'required' }}>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="contrasenaConfirmacion">Repite la contraseña</label>
                    <input type="password" name="contrasenaConfirmacion" id="contrasenaConfirmacion" minlength="8" autocomplete="new-password" {{ $editando ? '' : 'required' }}>
                </div>
            </div>
        </fieldset>

        <label class="casilla-destacada">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" {{ old('activo', $cuenta->activo) ? 'checked' : '' }}>
            Cuenta activa (puede iniciar sesión)
        </label>

        <div class="acciones-formulario">
            <a class="boton-secundario" href="{{ route('usuarios.index') }}">Cancelar</a>
            <button type="submit" class="boton-azul">{{ $editando ? 'Guardar cambios' : 'Crear cuenta' }}</button>
        </div>
    </form>

<script src="{{ recurso('js/usuarios.js') }}"></script>
@include('partials.footerPanel')
