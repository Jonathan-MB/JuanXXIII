@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/inscritos.css') }}">
<title>Pagos · Juan XXIII</title>

@include('partials.header')

    @if (! $retiro)
        <h1 class="vista-titulo">Pagos</h1>
        <p class="texto-vacio">No hay un retiro próximo.</p>
    @else
        <div class="cabecera-vista">
            <h1 class="vista-titulo">Pagos · {{ $retiro->nombre }}</h1>
            <a class="boton-secundario" href="{{ route('exportar.pagos') }}">Descargar pagos para Excel</a>
        </div>
        @include('partials.mensaje')

        {{-- --------------------RESUMEN------------------- --}}
        <section class="resumen-pagos resumen-tesoreria">
            <div><p class="cifra-numero">{{ '$' . number_format($resumen['recaudado'], 0, ',', '.') }}</p><p class="cifra-etiqueta">recaudado en el retiro</p></div>
            <div><p class="cifra-numero">{{ $resumen['completo'] }}</p><p class="cifra-etiqueta">pagaron todo</p></div>
            <div><p class="cifra-numero">{{ $resumen['abono'] }}</p><p class="cifra-etiqueta">con abono</p></div>
            <div><p class="cifra-numero">{{ $resumen['sin_pago'] }}</p><p class="cifra-etiqueta">sin pago</p></div>
        </section>

        {{-- --------------------FILTROS------------------- --}}
        <form class="filtros-inscritos" method="GET" action="{{ route('pagos.index') }}">
            <input class="buscador" type="search" name="busqueda" value="{{ $busqueda }}" placeholder="Nombre, cédula o padrino…" aria-label="Buscar por nombre, cédula o padrino">
            <select name="pago" aria-label="Filtrar por pago">
                <option value="">Todos los pagos</option>
                <option value="completo" {{ $pago === 'completo' ? 'selected' : '' }}>Pagaron todo</option>
                <option value="abono" {{ $pago === 'abono' ? 'selected' : '' }}>Con abono</option>
                <option value="sin_pago" {{ $pago === 'sin_pago' ? 'selected' : '' }}>Sin pago</option>
            </select>
            <button type="submit" class="boton-azul">Buscar</button>
            @if ($busqueda !== '' || $pago)
                <a class="enlace-accion" href="{{ route('pagos.index') }}">Quitar filtros</a>
            @endif
        </form>

        @if ($inscritos->isEmpty())
            <p class="texto-vacio">No hay inscritos {{ $busqueda !== '' || $pago ? 'con esos filtros' : 'todavía' }}.</p>
        @else
            <div class="contenedor-tabla">
                <table class="tabla-general">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Celular</th>
                            <th>Retiro</th>
                            <th>Padrino</th>
                            <th>Pagado</th>
                            <th>Falta</th>
                            <th>Pago</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inscritos as $inscrito)
                            <tr class="{{ $inscrito->estaFuera() ? 'fila-fuera' : '' }}">
                                <td><a class="enlace-inscrito" href="{{ route('inscritos.show', $inscrito) }}#pagos">{{ $inscrito->persona->apellidos }}, {{ $inscrito->persona->nombres }}</a></td>
                                <td>{{ $inscrito->persona->cedula }}</td>
                                <td class="dato-sin-salto">{{ $inscrito->persona->celular }}</td>
                                <td>{{ $inscrito->persona->sexo === 'Femenino' ? 'M ' . $retiro->numero_mujeres : 'H ' . $retiro->numero_hombres }}</td>
                                <td>
                                    @if ($inscrito->tiene_padrino)
                                        {{ $inscrito->padrino_nombre }}
                                        @if ($inscrito->padrino_telefono)
                                            <br><span class="dato-sin-salto dato-secundario">{{ $inscrito->padrino_telefono }}</span>
                                        @endif
                                    @else
                                        <span class="dato-secundario">Sin padrino</span>
                                    @endif
                                </td>
                                <td class="dato-valor">{{ '$' . number_format($inscrito->total_pagado, 0, ',', '.') }}</td>
                                <td class="dato-valor">{{ '$' . number_format($inscrito->saldo, 0, ',', '.') }}</td>
                                <td>@include('partials.chipPago', ['inscrito' => $inscrito])</td>
                                <td><span class="chip {{ $inscrito->estaFuera() ? 'chip-rojo' : 'chip-gris' }}">{{ $inscrito->nombre_estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="texto-ayuda">Aquí aparecen también quienes no asistieron o abandonaron, para no perder el registro de lo que pagaron. Haz clic en un nombre para registrar o corregir pagos.</p>
        @endif
    @endif

@include('partials.footerPanel')
