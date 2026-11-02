<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{

    //  Tope de sillas por mesa. La hoja de ubicación trae 12, pero el
    // número es variable; 20 deja margen sin permitir errores de tecleo.
    public const MAXIMO_SILLAS = 20;

    protected $fillable = [
        'sexo',
        'numero',
        'nombre',
        'cantidad_sillas',
        'catequista_titular',
        'titular_celular',
        'titular_ciudad',
        'titular_barrio',
        'catequista_auxiliar',
        'auxiliar_celular',
        'auxiliar_ciudad',
        'auxiliar_barrio',
        'retiro_id',
    ];



    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }



    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'mesa_id');
    }
}
