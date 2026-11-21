@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/impresiones.css') }}">
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
                <p class="texto-ayuda texto-ayuda-arriba">Va en el encabezado del directorio. El CR (catequista rotativo) es opcional: deja el nombre vacío si no hay. El cumpleaños sirve para la hoja de cumpleaños.</p>

                <div class="contenedor-tabla">
                    <table class="tabla-general tabla-datos">
                        <thead>
                            <tr><th>Cargo</th><th>Nombre</th><th>Ciudad / barrio</th><th>Teléfono</th><th>Cumpleaños</th></tr>
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
                                    <td><input type="tel" inputmode="numeric" data-solo-numeros name="cargos[{{ $i }}][telefono]" maxlength="20" value="{{ $fila?->telefono }}" aria-label="Teléfono del {{ $cargo }}"></td>
                                    <td><input type="date" name="cargos[{{ $i }}][fecha_nacimiento]" value="{{ $fila?->fecha_nacimiento?->format('Y-m-d') }}" max="{{ now()->toDateString() }}" aria-label="Cumpleaños del {{ $cargo }}"></td>
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
                            <td><input type="tel" inputmode="numeric" data-solo-numeros name="firmantes[{{ $i }}][celular]" maxlength="20" value="{{ $firmantes[$i]->celular ?? '' }}" aria-label="Celular de la firma {{ $i + 1 }}"></td>
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
    <section class="bloque-formulario bloque-datos" id="perseverancias">
        <h2 class="titulo-bloque-datos">Perseverancias (pie del directorio)</h2>
        <p class="texto-ayuda">Ahora tienen su propia sección, donde también se elige qué sale en el inicio.</p>
        <div class="acciones-formulario">
            <a class="boton-secundario" href="{{ route('perseverancias.index') }}">Ir a Perseverancias</a>
        </div>
    </section>

@include('partials.footerPanel')
