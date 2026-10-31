<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadBase extends Model
{

    //  La jornada va de las 4:00 a. m. a las 3:59 a. m. del día siguiente:
    // las oraciones de las 12:30 a. m. siguen siendo del viernes.
    public const HORA_CORTE = '04:00:00';

    public const JORNADAS = [
        0 => 'Viernes · Día de reflexión',
        1 => 'Sábado · Día de conversión',
        2 => 'Domingo · Día de apostolado',
    ];

    protected $fillable = [
        'sexo',
        'dia_relativo',
        'hora_inicio',
        'hora_fin',
        'nombre',
        'es_charla',
    ];

    protected $casts = [
        'dia_relativo' => 'integer',
        'es_charla'    => 'boolean',
    ];



    // Para ordenar dentro de la jornada: la madrugada va al final
    public function getOrdenAttribute(): string
    {
        return ($this->hora_inicio < self::HORA_CORTE ? '1' : '0') . $this->hora_inicio;
    }
}
