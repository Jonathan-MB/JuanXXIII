<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{

    // entrada suma, salida resta, ajuste deja la cantidad en un valor exacto
    public const ENTRADA = 'entrada';
    public const SALIDA  = 'salida';
    public const AJUSTE  = 'ajuste';

    protected $fillable = [
        'tipo',
        'cantidad',
        'cantidad_resultante',
        'nota',
        'articulo_id',
        'usuario_id',
    ];



    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }



    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
