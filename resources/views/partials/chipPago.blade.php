{{-- Chip del estado de pago de una inscripción: verde completo, amarillo abono, rojo sin pago --}}
@if ($inscrito->estado_pago === 'completo')
    <span class="chip chip-verde">Pagó todo</span>
@elseif ($inscrito->estado_pago === 'abono')
    <span class="chip chip-amarillo">Abonó {{ '$' . number_format($inscrito->total_pagado, 0, ',', '.') }}</span>
@else
    <span class="chip chip-rojo">Sin pago</span>
@endif
