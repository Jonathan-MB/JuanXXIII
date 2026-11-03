{{-- Dibujo pequeño de cada parte del día: luna con estrellas, sol saliendo, sol y luna --}}
<svg class="icono-periodo" viewBox="0 0 24 24" aria-hidden="true">
    @switch($periodo)
        @case('madrugada')
            <path d="M15 4a8 8 0 1 0 5 13.5A6.5 6.5 0 0 1 15 4z" fill="currentColor"/>
            <circle cx="6" cy="5" r="1.2" fill="currentColor"/>
            <circle cx="9.5" cy="2.5" r="0.9" fill="currentColor"/>
            @break
        @case('manana')
            <path d="M5 17a7 7 0 0 1 14 0z" fill="currentColor"/>
            <path d="M12 4v4M4.2 9.2l2.6 2.2M19.8 9.2l-2.6 2.2M2 20h20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            @break
        @case('tarde')
            <circle cx="12" cy="12" r="5" fill="currentColor"/>
            <path d="M12 1v3M12 20v3M1 12h3M20 12h3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            @break
        @case('noche')
            <path d="M14 3a9 9 0 1 0 7 14.5A7 7 0 0 1 14 3z" fill="currentColor"/>
            @break
    @endswitch
</svg>
