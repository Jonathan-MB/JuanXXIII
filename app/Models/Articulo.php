<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Articulo extends Model
{

    //  "1 unidad", "3 unidades", "1 kilo": la unidad en singular cuando es uno
    public static function cantidadConUnidad($cantidad, ?string $unidad): string
    {
        $unidad = (string) $unidad;

        if ((float) $cantidad == 1 && $unidad !== '') {
            $unidad = str_ends_with($unidad, 'des') ? substr($unidad, 0, -2)
                : (str_ends_with($unidad, 's') ? substr($unidad, 0, -1) : $unidad);
        }

        return trim($cantidad . ' ' . $unidad);
    }


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
