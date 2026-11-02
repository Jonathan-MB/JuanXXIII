<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SantisimoRegla extends Model
{

    // Horas que el equipo siempre cubre, u horas en las que no puede ir
    public const FIJA      = 'fija';
    public const PROHIBIDA = 'prohibida';

    protected $fillable = [
        'tipo',
        'dia_relativo',
        'hora_desde',
        'hora_hasta',
        'equipo_id',
    ];

    protected $casts = [
        'dia_relativo' => 'integer',
    ];



    //  La franja que empieza a esa hora cae dentro de la regla. El final
    // no se incluye: de 0:00 a 4:00 cubre hasta la franja de las 3:30.
    public function incluye(int $diaRelativo, string $hora): bool
    {
        if ($this->dia_relativo !== null && $this->dia_relativo !== $diaRelativo) {
            return false;
        }

        return $hora >= $this->hora_desde && $hora < $this->hora_hasta;
    }



    public function getDiaTextoAttribute(): string
    {
        return [0 => 'Viernes', 1 => 'Sábado', 2 => 'Domingo'][$this->dia_relativo] ?? 'Todos los días';
    }



    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}
