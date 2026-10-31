<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Articulo extends Model
{
    protected $fillable = [
        'nombre',
        'unidad',
        'cantidad',
        'por_comprar',
        'estado',
        'equipo_id',
    ];

    protected $casts = [
        'cantidad'    => 'integer',
        'por_comprar' => 'integer',
    ];



    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }



    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'articulo_id');
    }
}
