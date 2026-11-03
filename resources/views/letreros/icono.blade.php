{{--  Dibujos de los letreros, en SVG para que se impriman nítidos a
     cualquier tamaño. Azul y dorado del movimiento; rojo solo en las
     señales de prohibido. $icono dice cuál; $clase, el tamaño.
     Todo en contorno, con el relleno blanco: así no se gasta tinta. --}}
@php
    $azul   = '#1c2f5e';
    $dorado = '#e3c400';
    $rojo   = '#c62828';
@endphp
<svg class="{{ $clase ?? 'icono-letrero' }}" viewBox="0 0 200 200" aria-hidden="true">
    @switch($icono)
        @case('silencio')
            {{-- Altavoz con una raya: sin ruido --}}
            <circle cx="100" cy="100" r="92" fill="none" stroke="{{ $azul }}" stroke-width="8"/>
            <path d="M48 82 h22 l32 -26 v88 l-32 -26 h-22 z" fill="#ffffff" stroke="{{ $azul }}" stroke-width="8" stroke-linejoin="round"/>
            <path d="M122 76 q20 24 0 48 M136 64 q32 36 0 72" fill="none" stroke="{{ $dorado }}" stroke-width="8" stroke-linecap="round"/>
            <line x1="38" y1="160" x2="162" y2="40" stroke="{{ $rojo }}" stroke-width="12" stroke-linecap="round"/>
            @break

        @case('custodia')
            {{-- Custodia: rayos dorados, la hostia y la cruz arriba --}}
            @for ($i = 0; $i < 24; $i++)
                <line x1="100" y1="78" x2="{{ 100 + 66 * cos(deg2rad($i * 15)) }}" y2="{{ 78 + 66 * sin(deg2rad($i * 15)) }}" stroke="{{ $dorado }}" stroke-width="{{ $i % 2 ? 3 : 6 }}" stroke-linecap="round"/>
            @endfor
            <circle cx="100" cy="78" r="36" fill="#ffffff" stroke="{{ $azul }}" stroke-width="5"/>
            <circle cx="100" cy="78" r="22" fill="#ffffff" stroke="{{ $dorado }}" stroke-width="4"/>
            <path d="M100 2 v22 M91 11 h18" stroke="{{ $azul }}" stroke-width="5" stroke-linecap="round"/>
            <path d="M100 114 v30" stroke="{{ $azul }}" stroke-width="8" stroke-linecap="round"/>
            <ellipse cx="100" cy="146" rx="12" ry="6" fill="#ffffff" stroke="{{ $azul }}" stroke-width="3"/>
            <path d="M70 186 q30 -34 60 0 z" fill="#ffffff" stroke="{{ $azul }}" stroke-width="5" stroke-linejoin="round"/>
            @break

        @case('cepillo')
            {{-- Cepillo de dientes tachado --}}
            <g transform="rotate(-35 100 100)">
                <rect x="34" y="94" width="104" height="14" rx="7" fill="#ffffff" stroke="{{ $azul }}" stroke-width="5"/>
                <rect x="136" y="88" width="34" height="20" rx="4" fill="#ffffff" stroke="{{ $azul }}" stroke-width="5"/>
                @for ($i = 0; $i < 6; $i++)
                    <line x1="{{ 140 + $i * 5.5 }}" y1="88" x2="{{ 140 + $i * 5.5 }}" y2="70" stroke="{{ $azul }}" stroke-width="3"/>
                @endfor
                <path d="M138 70 q18 -10 32 0" fill="none" stroke="{{ $dorado }}" stroke-width="7" stroke-linecap="round"/>
            </g>
            <circle cx="100" cy="100" r="88" fill="none" stroke="{{ $rojo }}" stroke-width="14"/>
            <line x1="38" y1="38" x2="162" y2="162" stroke="{{ $rojo }}" stroke-width="14"/>
            @break

        @case('inodoro')
        @case('inodoroPapel')
            {{-- Inodoro tachado (con papeles cayendo si es "no arrojar papeles") --}}
            <rect x="64" y="34" width="72" height="44" rx="8" fill="#ffffff" stroke="{{ $azul }}" stroke-width="8"/>
            <path d="M48 86 h104 q-4 40 -40 48 l6 34 h-36 l6 -34 q-36 -8 -40 -48 z" fill="#ffffff" stroke="{{ $azul }}" stroke-width="8" stroke-linejoin="round"/>
            @if ($icono === 'inodoroPapel')
                <rect x="140" y="18" width="30" height="22" rx="3" fill="#ffffff" stroke="{{ $dorado }}" stroke-width="4" transform="rotate(20 155 29)"/>
                <rect x="28" y="22" width="26" height="20" rx="3" fill="#ffffff" stroke="{{ $dorado }}" stroke-width="4" transform="rotate(-18 41 32)"/>
            @endif
            <circle cx="100" cy="100" r="88" fill="none" stroke="{{ $rojo }}" stroke-width="14"/>
            <line x1="38" y1="38" x2="162" y2="162" stroke="{{ $rojo }}" stroke-width="14"/>
            @break

        @case('caneca')
        @case('basura')
            {{-- Caneca: con una manzana para los restos de comida, con un papel arrugado para la basura --}}
            <rect x="52" y="52" width="96" height="12" rx="6" fill="#ffffff" stroke="{{ $azul }}" stroke-width="6"/>
            <rect x="86" y="40" width="28" height="14" rx="5" fill="#ffffff" stroke="{{ $azul }}" stroke-width="6"/>
            <path d="M60 70 h80 l-8 112 h-64 z" fill="#ffffff" stroke="{{ $azul }}" stroke-width="8" stroke-linejoin="round"/>
            <path d="M84 88 v80 M100 88 v80 M116 88 v80" stroke="{{ $azul }}" stroke-width="5" stroke-linecap="round"/>
            @if ($icono === 'caneca')
                <circle cx="152" cy="34" r="20" fill="#ffffff" stroke="{{ $dorado }}" stroke-width="6"/>
                <path d="M152 14 q4 -8 12 -8" fill="none" stroke="{{ $azul }}" stroke-width="4" stroke-linecap="round"/>
            @else
                <path d="M136 18 l14 -8 l12 6 l10 -4 l6 14 l-6 12 l4 12 l-14 6 l-12 -6 l-12 4 l-6 -14 l4 -10 z" fill="#ffffff" stroke="{{ $dorado }}" stroke-width="5" stroke-linejoin="round"/>
                <path d="M146 26 l10 6 M158 20 l2 14 M150 40 l14 -4" stroke="{{ $azul }}" stroke-width="3" stroke-linecap="round"/>
            @endif
            @break

        @case('sobre')
            {{-- Sobre con corazón: las cartas de Ayudas de Dios --}}
            <rect x="20" y="50" width="160" height="110" rx="10" fill="#ffffff" stroke="{{ $azul }}" stroke-width="8"/>
            <path d="M24 56 l76 58 l76 -58" fill="none" stroke="{{ $azul }}" stroke-width="8" stroke-linejoin="round"/>
            <path d="M100 150 c-30 -18 -34 -40 -18 -46 c8 -3 15 1 18 8 c3 -7 10 -11 18 -8 c16 6 12 28 -18 46 z" fill="#ffffff" stroke="{{ $dorado }}" stroke-width="5"/>
            @break

        @case('celular')
            {{-- Celular con un signo de pesos: pago por transferencia --}}
            <rect x="58" y="10" width="84" height="180" rx="16" fill="#ffffff" stroke="{{ $azul }}" stroke-width="8"/>
            <path d="M62 30 h76 M62 160 h76" stroke="{{ $azul }}" stroke-width="5"/>
            <circle cx="100" cy="175" r="6" fill="none" stroke="{{ $azul }}" stroke-width="4"/>
            <circle cx="100" cy="95" r="26" fill="#ffffff" stroke="{{ $dorado }}" stroke-width="6"/>
            <text x="100" y="108" text-anchor="middle" font-family="Montserrat, Arial, sans-serif" font-size="36" font-weight="800" fill="{{ $azul }}">$</text>
            @break

        @case('hombre')
        @case('mujer')
            {{-- Figura de baño: pantalón para hombres, vestido para mujeres. $color la pinta --}}
            <circle cx="100" cy="34" r="22" fill="#ffffff" stroke="{{ $color ?? $azul }}" stroke-width="7"/>
            @if ($icono === 'hombre')
                <path d="M68 64 h64 a10 10 0 0 1 10 10 v52 a8 8 0 0 1 -16 0 v-44 h-4 v106 a10 10 0 0 1 -20 0 v-60 h-4 v60 a10 10 0 0 1 -20 0 v-106 h-4 v44 a8 8 0 0 1 -16 0 v-52 a10 10 0 0 1 10 -10 z" fill="#ffffff" stroke="{{ $color ?? $azul }}" stroke-width="7" stroke-linejoin="round"/>
            @else
                <path d="M76 64 h48 a10 10 0 0 1 9 6 l20 50 a8 8 0 0 1 -15 6 l-14 -32 l24 64 h-26 v34 a9 9 0 0 1 -18 0 v-34 h-8 v34 a9 9 0 0 1 -18 0 v-34 h-26 l24 -64 l-14 32 a8 8 0 0 1 -15 -6 l20 -50 a10 10 0 0 1 9 -6 z" fill="#ffffff" stroke="{{ $color ?? $azul }}" stroke-width="7" stroke-linejoin="round"/>
            @endif
            @break

        @case('bebida')
            {{-- Vaso con pitillo: la raya dorada es el nivel de la bebida --}}
            <path d="M52 52 h96 l-12 132 a8 8 0 0 1 -8 7 h-56 a8 8 0 0 1 -8 -7 z" fill="#ffffff" stroke="{{ $color ?? $azul }}" stroke-width="8" stroke-linejoin="round"/>
            <path d="M62 100 q19 -10 38 0 t38 0" fill="none" stroke="{{ $dorado }}" stroke-width="7" stroke-linecap="round"/>
            <path d="M112 72 l26 -60 h22" fill="none" stroke="{{ $color ?? $azul }}" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="84" cy="134" r="6" fill="none" stroke="{{ $dorado }}" stroke-width="4"/>
            <circle cx="106" cy="156" r="4" fill="none" stroke="{{ $dorado }}" stroke-width="4"/>
            @break

        @case('caja')
            {{-- Caja de regalo con moño --}}
            <rect x="30" y="86" width="140" height="96" rx="6" fill="#ffffff" stroke="{{ $azul }}" stroke-width="8"/>
            <rect x="22" y="64" width="156" height="26" rx="6" fill="#ffffff" stroke="{{ $azul }}" stroke-width="8"/>
            <path d="M100 64 v118" stroke="{{ $dorado }}" stroke-width="10"/>
            <path d="M100 64 c-40 -40 -60 -4 -30 2 z M100 64 c40 -40 60 -4 30 2 z" fill="#ffffff" stroke="{{ $dorado }}" stroke-width="6" stroke-linejoin="round"/>
            @break
    @endswitch
</svg>
