@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/retiros.css') }}">
<title>Retiros · Juan XXIII</title>

@include('partials.header')

    <div class="cabecera-vista">
        <h1 class="vista-titulo">Retiros</h1>
        <a class="boton-azul" href="{{ route('retiros.create') }}">+ Nuevo retiro</a>
    </div>
    @include('partials.mensaje')

    @if ($retiros->isEmpty())
        <p class="texto-vacio">Todavía no hay retiros. Crea el primero con el botón <strong>+ Nuevo retiro</strong>.</p>
    @else
        <div class="contenedor-tabla">
            <table class="tabla-general">
                <thead>
                    <tr>
                        <th>Retiro</th>
                        <th>Fechas</th>
                        <th>Lugar</th>
                        <th>Ofrenda</th>
                        <th>Inscripciones</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($retiros as $retiro)
                        <tr>
                            <td class="dato-retiro-numero">{{ $retiro->numero_hombres }} H · {{ $retiro->numero_mujeres }} M</td>
                            <td>{{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</td>
                            <td>{{ $retiro->lugar }}</td>
                            <td>{{ $retiro->ofrenda_texto }}</td>
                            <td>
                                <form method="POST" action="{{ route('retiros.update', $retiro) }}" class="formulario-interruptor">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="inscripcionesAbiertas" value="{{ $retiro->inscripciones_abiertas ? 0 : 1 }}">
                                    @if ($retiro->inscripciones_abiertas)
                                        <button type="submit" class="chip chip-verde chip-boton" title="Cerrar inscripciones">Abiertas</button>
                                    @else
                                        <button type="submit" class="chip chip-gris chip-boton" title="Abrir inscripciones">Cerradas</button>
                                    @endif
                                </form>
                            </td>
                            <td><a class="enlace-accion" href="{{ route('retiros.edit', $retiro) }}">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="texto-ayuda">Haz clic en <em>Abiertas</em> o <em>Cerradas</em> para abrir o cerrar las inscripciones. La página pública muestra el próximo retiro con inscripciones abiertas.</p>
    @endif

@include('partials.footerPanel')
