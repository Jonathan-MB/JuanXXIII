<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SantisimoFranja extends Model
{

    // Cada franja dura media hora
    public const MINUTOS = 30;

    protected $fillable = [
        'inicio',
        'todos',
        'actividad',
        'retiro_id',
        'equipo_id',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'todos'  => 'boolean',
    ];



    public function getFinAttribute()
    {
        return $this->inicio->copy()->addMinutes(self::MINUTOS);
    }



    public function getActividadTextoAttribute(): string
    {
        return $this->actividad ?: 'Adoración';
    }



    // Nadie la cubre: el Santísimo nunca debe quedar solo
    public function estaVacia(): bool
    {
        return ! $this->todos && ! $this->equipo_id;
    }



    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }



    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}
