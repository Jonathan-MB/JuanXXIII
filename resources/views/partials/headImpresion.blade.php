<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('img/icono.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('css/impresion.css') }}">
    <title>{{ $titulo }}{{ isset($retiro) ? ' · ' . $retiro->nombre : '' }}</title>
</head>

<body>

    {{-- Solo se ve en pantalla: al imprimir desaparece. El inventario no depende del retiro y vuelve a su propia página --}}
    <div class="barra-impresion">
        <p class="barra-titulo">{{ $titulo }}{{ isset($retiro) ? ' · ' . $retiro->nombre : '' }}</p>
        <div class="barra-botones">
            <button type="button" class="barra-boton barra-imprimir" id="boton-imprimir">Imprimir</button>
            <a class="barra-boton" href="{{ $volver ?? route('impresiones.index') }}">{{ $volverTexto ?? 'Volver a impresiones' }}</a>
        </div>
    </div>
