{{-- Membrete y datos del retiro de cada informe. $cargo es quien lo llena (su nombre sale escrito si ya está en el rectorado) --}}
@php
    $esMujer = $sexo === 'Femenino';
    $nombre  = $cargo ? $grupo['rectorado']->firstWhere('cargo', $cargo)?->nombre : null;
@endphp
@include('partials.membrete', ['subtitulo' => $titulo . ' · ' . ($esMujer ? 'Mujeres' : 'Hombres')])
<p class="datos-informe">
    <span><strong>Retiro N.º</strong> {{ $retiro->numeroPara($sexo) }}</span>
    <span><strong>Ciudad:</strong> Cali</span>
    <span><strong>Fecha:</strong> {{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</span>
    @if ($cargo)
        <span class="dato-nombre"><strong>{{ $cargo }}:</strong> <span class="linea-llenar">{{ $nombre }}</span></span>
    @endif
</p>
