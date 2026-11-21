@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/llegada.css') }}">
<title>Nombres de mesas · Juan XXIII</title>

@include('partials.header')

    <a class="enlace-volver" href="{{ route('mesas.index') }}">← Volver a mesas</a>
    <h1 class="vista-titulo">Nombres de mesas</h1>
    @include('partials.mensaje')

    <p class="texto-ayuda texto-ayuda-mesas">
        El nombre (santo) de cada mesa según su número. Casi nunca cambia, así que sirve para todos los retiros:
        cuando se crea una mesa sin nombre, ya llega con el de aquí. Si agregan una mesa nueva, escribe su santo en el renglón de su número.
        Dejar un renglón vacío quita ese nombre.
    </p>

    <form method="POST" action="{{ route('mesas.guardarNombres') }}">
        @csrf
        @method('PUT')

        <div class="rejilla-nombres-mesa">
            @foreach (['Masculino' => 'Hombres', 'Femenino' => 'Mujeres'] as $sexo => $grupo)
                <section class="bloque-formulario bloque-nombres-mesa">
                    <h2 class="titulo-bloque-datos">{{ $grupo }}</h2>
                    <table class="tabla-general tabla-nombres-mesa">
                        <thead>
                            <tr><th>Mesa</th><th>Nombre</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($filas[$sexo] as $numero => $nombre)
                                <tr>
                                    <td class="numero-nombre-mesa"><label for="nombre-{{ $sexo }}-{{ $numero }}">{{ $numero }}</label></td>
                                    <td><input type="text" id="nombre-{{ $sexo }}-{{ $numero }}" name="nombres[{{ $sexo }}][{{ $numero }}]" maxlength="60" value="{{ old('nombres.' . $sexo . '.' . $numero, $nombre) }}" placeholder="{{ $sexo === 'Femenino' ? 'Santa…' : 'San…' }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endforeach
        </div>

        @if ($retiro)
            <label class="opcion-nombres-mesa">
                <input type="checkbox" name="ponerEnEsteRetiro" value="1" checked>
                Poner también estos nombres a las mesas de {{ $retiro->nombre }} que están sin nombre
            </label>
        @endif

        <div class="acciones-formulario">
            <button type="submit" class="boton-azul">Guardar nombres</button>
        </div>
    </form>

@include('partials.footerPanel')
