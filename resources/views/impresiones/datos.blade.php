@include('partials.head')

<link rel="stylesheet" href="{{ asset('css/impresiones.css') }}">
<title>Datos para imprimir · Juan XXIII</title>

@include('partials.header')

    <a class="enlace-volver" href="{{ route('impresiones.index') }}">← Volver a impresiones</a>
    <h1 class="vista-titulo">Datos para imprimir</h1>
    @include('partials.mensaje')

    @if ($retiro)
        {{-- --------------------RECTORADO------------------- --}}
        @foreach (['Masculino' => 'hombres', 'Femenino' => 'mujeres'] as $sexo => $grupo)
            @php
                $guardados = ($rectorados[$sexo] ?? collect())->values();
            @endphp
            <form class="bloque-formulario bloque-datos" id="rectorado-{{ $grupo }}" method="POST" action="{{ route('impresiones.rectorado') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="sexo" value="{{ $sexo }}">
                <h2 class="titulo-bloque-datos">Rectorado de {{ $grupo }} · Retiro {{ $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres }}</h2>
                <p class="texto-ayuda texto-ayuda-arriba">Va en el encabezado del directorio. El CR (catequista rotativo) es opcional: deja el nombre vacío si no hay.</p>

                <div class="contenedor-tabla">
                    <table class="tabla-general tabla-datos">
                        <thead>
                            <tr><th>Cargo</th><th>Nombre</th><th>Ciudad / barrio</th><th>Teléfono</th></tr>
                        </thead>
                        <tbody>
                            @foreach (\App\Models\Rectorado::CARGOS as $i => $cargo)
                                @php
                                    $fila = $guardados[$i] ?? null;
                                @endphp
                                <tr>
                                    <td class="dato-cargo">
                                        {{ $cargo }}
                                        <input type="hidden" name="cargos[{{ $i }}][cargo]" value="{{ $cargo }}">
                                    </td>
                                    <td><input type="text" name="cargos[{{ $i }}][nombre]" maxlength="100" value="{{ $fila?->nombre }}" aria-label="Nombre del {{ $cargo }}"></td>
                                    <td><input type="text" name="cargos[{{ $i }}][ciudad]" maxlength="60" value="{{ $fila?->ciudad }}" aria-label="Ciudad del {{ $cargo }}"></td>
                                    <td><input type="tel" name="cargos[{{ $i }}][telefono]" maxlength="20" value="{{ $fila?->telefono }}" aria-label="Teléfono del {{ $cargo }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="acciones-formulario">
                    <button type="submit" class="boton-azul">Guardar rectorado de {{ $grupo }}</button>
                </div>
            </form>
        @endforeach
    @endif

    {{-- --------------------FIRMAS DE LA CARTA------------------- --}}
    <form class="bloque-formulario bloque-datos" id="firmantes" method="POST" action="{{ route('impresiones.firmantes') }}">
        @csrf
        @method('PUT')
        <h2 class="titulo-bloque-datos">Firmas de la carta al presbítero</h2>
        <div class="contenedor-tabla">
            <table class="tabla-general tabla-datos">
                <thead>
                    <tr><th>Nombre</th><th>Cargo</th><th>Celular</th></tr>
                </thead>
                <tbody>
                    @foreach ([0, 1, 2] as $i)
                        <tr>
                            <td><input type="text" name="firmantes[{{ $i }}][nombre]" maxlength="100" value="{{ $firmantes[$i]->nombre ?? '' }}" aria-label="Nombre de la firma {{ $i + 1 }}"></td>
                            <td><input type="text" name="firmantes[{{ $i }}][cargo]" maxlength="100" value="{{ $firmantes[$i]->cargo ?? '' }}" aria-label="Cargo de la firma {{ $i + 1 }}"></td>
                            <td><input type="tel" name="firmantes[{{ $i }}][celular]" maxlength="20" value="{{ $firmantes[$i]->celular ?? '' }}" aria-label="Celular de la firma {{ $i + 1 }}"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="acciones-formulario">
            <button type="submit" class="boton-azul">Guardar firmas</button>
        </div>
    </form>

    {{-- --------------------PERSEVERANCIAS------------------- --}}
    <form class="bloque-formulario bloque-datos" id="perseverancias" method="POST" action="{{ route('impresiones.perseverancias') }}">
        @csrf
        @method('PUT')
        <h2 class="titulo-bloque-datos">Perseverancias (pie del directorio)</h2>
        <div class="contenedor-tabla">
            <table class="tabla-general tabla-datos">
                <thead>
                    <tr><th>Tipo</th><th>Día</th><th>Hora</th><th>Parroquia o lugar</th><th>Dirección</th><th></th></tr>
                </thead>
                <tbody id="filas-perseverancias">
                    @foreach ($perseverancias as $i => $perseverancia)
                        <tr>
                            <td><input type="text" name="perseverancias[{{ $i }}][tipo]" maxlength="40" value="{{ $perseverancia->tipo }}" aria-label="Tipo"></td>
                            <td><input type="text" name="perseverancias[{{ $i }}][dia]" maxlength="30" value="{{ $perseverancia->dia }}" aria-label="Día"></td>
                            <td><input type="text" name="perseverancias[{{ $i }}][hora]" maxlength="20" value="{{ $perseverancia->hora }}" aria-label="Hora"></td>
                            <td><input type="text" name="perseverancias[{{ $i }}][parroquia]" maxlength="120" value="{{ $perseverancia->parroquia }}" aria-label="Parroquia"></td>
                            <td><input type="text" name="perseverancias[{{ $i }}][direccion]" maxlength="120" value="{{ $perseverancia->direccion }}" aria-label="Dirección"></td>
                            <td><button type="button" class="boton-texto boton-quitar-fila">Quitar</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="acciones-formulario acciones-entre">
            <button type="button" class="boton-secundario" id="agregar-perseverancia">+ Agregar perseverancia</button>
            <button type="submit" class="boton-azul">Guardar perseverancias</button>
        </div>
    </form>

    <template id="plantilla-perseverancia">
        <tr>
            <td><input type="text" data-campo="tipo" maxlength="40" value="Perseverancia" aria-label="Tipo"></td>
            <td><input type="text" data-campo="dia" maxlength="30" aria-label="Día"></td>
            <td><input type="text" data-campo="hora" maxlength="20" aria-label="Hora"></td>
            <td><input type="text" data-campo="parroquia" maxlength="120" aria-label="Parroquia"></td>
            <td><input type="text" data-campo="direccion" maxlength="120" aria-label="Dirección"></td>
            <td><button type="button" class="boton-texto boton-quitar-fila">Quitar</button></td>
        </tr>
    </template>

<script src="{{ asset('js/impresiones.js') }}"></script>
@include('partials.footerPanel')
