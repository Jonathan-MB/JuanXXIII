{{-- Una línea de firma por cargo; si el rectorado ya tiene el nombre, va debajo de la línea --}}
<div class="firmas-informe">
    @foreach ($firmas as $cargo)
        @php
            $nombre = $grupo['rectorado']->firstWhere('cargo', $cargo)?->nombre;
        @endphp
        <div>
            <span class="linea-firma-informe"></span>
            @if ($nombre)
                <span class="nombre-firma">{{ $nombre }}</span>
            @endif
            {{ $cargo }}
        </div>
    @endforeach
</div>
