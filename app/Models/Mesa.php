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
        'titular_nacimiento',
        'catequista_auxiliar',
        'auxiliar_celular',
        'auxiliar_ciudad',
        'auxiliar_barrio',
        'auxiliar_nacimiento',
        'retiro_id',
    ];



    protected $casts = [
        'titular_nacimiento'  => 'date',
        'auxiliar_nacimiento' => 'date',
    ];



    //  Una mesa nueva sin nombre toma el de su número (Mesas › Nombres de mesas)
    protected static function booted(): void
    {
        static::creating(function (Mesa $mesa) {
            if (blank($mesa->nombre) && $mesa->sexo && $mesa->numero) {
                $mesa->nombre = NombreMesa::para($mesa->sexo, (int) $mesa->numero);
            }
        });
    }



    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }



    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'mesa_id');
    }
}
