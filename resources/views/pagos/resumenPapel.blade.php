@php
    //  Cuentas para Tesorería. Lo que falta se cuenta solo de quienes siguen
    // en el retiro: a quien no vino o se fue no se le cobra durante el retiro.
    $siguen   = $lista->reject(fn ($inscrito) => $inscrito->estaFuera());
    $fuera    = $lista->filter(fn ($inscrito) => $inscrito->estaFuera());
    $deben    = $siguen->filter(fn ($inscrito) => $inscrito->saldo > 0);
    $pesos    = fn ($valor) => '$' . number_format($valor, 0, ',', '.');
    $personas = fn ($cuantas) => $cuantas . ($cuantas === 1 ? ' persona' : ' personas');
@endphp
<table class="tabla-impresion resumen-pagos-papel">
    <caption>Cuentas · {{ $titulo }}</caption>
    <tr>
        <th>Recogido</th>
        <td>{{ $pesos($lista->sum(fn ($inscrito) => $inscrito->total_pagado)) }}</td>
        <th>Pagaron todo</th>
        <td>{{ $personas($siguen->where('estado_pago', 'completo')->count()) }}</td>
    </tr>
    <tr>
        <th>Falta por recoger</th>
        <td>{{ $pesos($deben->sum(fn ($inscrito) => $inscrito->saldo)) }}</td>
        <th>Deben</th>
        <td>{{ $personas($deben->count()) }} ({{ $deben->where('estado_pago', 'sin_pago')->count() }} sin ningún pago)</td>
    </tr>
    <tr>
        <th>No asistieron o se fueron</th>
        <td colspan="3">{{ $personas($fuera->count()) }} · pagaron {{ $pesos($fuera->sum(fn ($inscrito) => $inscrito->total_pagado)) }} (no se les cobra lo que falta)</td>
    </tr>
</table>
