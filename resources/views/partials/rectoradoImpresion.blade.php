{{-- Los nombres del rectorado de un sexo, debajo del membrete --}}
@if ($rectorado->isNotEmpty())
    <p class="rectorado-desarrollo">
        @foreach ($rectorado as $persona)
            <span><strong>{{ $persona->cargo }}:</strong> {{ $persona->nombre }}</span>
        @endforeach
    </p>
@endif
