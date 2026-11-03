<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SantisimoFranja extends Model
{

    // Cada franja dura media hora
    public const MINUTOS = 30;

    //  Partes del día, para que el horario marque bien dónde empieza la
    // noche o la madrugada. clave => [nombre, desde la hora]
    public const PERIODOS = [
        'madrugada' => ['Madrugada', 0],
        'manana'    => ['Mañana', 6],
        'tarde'     => ['Tarde', 12],
        'noche'     => ['Noche', 18],
    ];

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



    // "madrugada", "manana", "tarde" o "noche", según la hora de inicio
    public function getPeriodoAttribute(): string
    {
        $hora = (int) $this->inicio->format('G');

        return collect(self::PERIODOS)->filter(fn ($periodo) => $hora >= $periodo[1])->keys()->last();
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
