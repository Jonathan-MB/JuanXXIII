{{-- Dentro de una carpeta del rectorado, la página (y la barra) la pone la carpeta --}}
@unless ($enCarpeta ?? false)
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('img/icono.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ recurso('css/impresion.css') }}">
    @isset($estilo)
        <link rel="stylesheet" href="{{ recurso($estilo) }}">
    @endisset
    <title>{{ $titulo }}{{ isset($retiro) ? ' · ' . $retiro->nombre : '' }}</title>
</head>

{{-- ?miniatura=1: dentro de la galería de letreros, solo la hoja, sin barra --}}
<body class="{{ request()->boolean('miniatura') ? 'miniatura' : '' }}">

    {{-- Solo se ve en pantalla: al imprimir desaparece. El inventario no depende del retiro y vuelve a su propia página --}}
    <div class="barra-impresion">
        <p class="barra-titulo">{{ $titulo }}{{ isset($retiro) ? ' · ' . $retiro->nombre : '' }}</p>
        <div class="barra-botones">
            <button type="button" class="barra-boton barra-imprimir" id="boton-imprimir">Imprimir</button>
            @if (! isset($volver) && auth()->user()?->tieneRol(\App\Models\Rol::DIRECTIVOS) && ! auth()->user()->tieneRol(\App\Models\Rol::ADMINISTRACION, \App\Models\Rol::SECRETARIA))
                <a class="barra-boton" href="{{ route('directivos.index') }}">Volver</a>
            @else
                <a class="barra-boton" href="{{ $volver ?? route('impresiones.index') }}">{{ $volverTexto ?? 'Volver a impresiones' }}</a>
            @endif
        </div>
    </div>
@endunless
