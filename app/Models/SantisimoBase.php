<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SantisimoBase extends Model
{
    protected $fillable = [
        'dia_relativo',
        'hora_inicio',
        'todos',
        'actividad',
        'equipo_id',
    ];

    protected $casts = [
        'dia_relativo' => 'integer',
        'todos'        => 'boolean',
    ];



    // Lo que se hace en la franja: si no dice nada, es adoración
    public function getActividadTextoAttribute(): string
    {
        return $this->actividad ?: 'Adoración';
    }



    public function estaVacia(): bool
    {
        return ! $this->todos && ! $this->equipo_id;
    }



    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}
