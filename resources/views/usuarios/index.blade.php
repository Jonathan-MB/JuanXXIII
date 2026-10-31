@include('partials.head')

<link rel="stylesheet" href="{{ asset('css/usuarios.css') }}">
<title>Cuentas · Juan XXIII</title>

@include('partials.header')

    <div class="cabecera-vista">
        <h1 class="vista-titulo">Cuentas</h1>
        <a class="boton-azul" href="{{ route('usuarios.create') }}">+ Nueva cuenta</a>
    </div>
    @include('partials.mensaje')

    <div class="contenedor-tabla">
        <table class="tabla-general">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Equipo</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $cuenta)
                    <tr class="{{ $cuenta->activo ? '' : 'fila-inactiva' }}">
                        <td class="dato-cuenta-nombre">{{ $cuenta->nombre }}</td>
                        <td>{{ $cuenta->usuario }}</td>
                        <td>{{ $cuenta->rol?->nombre }}</td>
                        <td>{{ $cuenta->equipo?->nombre ?? '—' }}</td>
                        <td>
                            @if ($cuenta->is(auth()->user()))
                                <span class="chip chip-azul">Tu cuenta</span>
                            @else
                                <form method="POST" action="{{ route('usuarios.update', $cuenta) }}" class="formulario-interruptor">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="activo" value="{{ $cuenta->activo ? 0 : 1 }}">
                                    @if ($cuenta->activo)
                                        <button type="submit" class="chip chip-verde chip-boton" title="Desactivar cuenta">Activa</button>
                                    @else
                                        <button type="submit" class="chip chip-gris chip-boton" title="Activar cuenta">Inactiva</button>
                                    @endif
                                </form>
                            @endif
                        </td>
                        <td><a class="enlace-accion" href="{{ route('usuarios.edit', $cuenta) }}">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="texto-ayuda">Haz clic en <em>Activa</em> o <em>Inactiva</em> para cambiar el estado. Una cuenta inactiva no puede iniciar sesión, pero no se borra.</p>

@include('partials.footerPanel')
