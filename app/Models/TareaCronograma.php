<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TareaCronograma extends Model
{

    // Una tarea se pone en rojo cuando falta esto o menos para su hora
    public const MINUTOS_ALERTA = 60;

    protected $fillable = [
        'dia_relativo',
        'hora',
        'descripcion',
        'responsable',
    ];

    protected $casts = [
        'dia_relativo' => 'integer',
    ];



    // "Viernes", "Sábado", "Domingo" o "3 días antes"
    public static function nombreDia(int $diaRelativo): string
    {
        return match (true) {
            $diaRelativo === 0 => 'Viernes',
            $diaRelativo === 1 => 'Sábado',
            $diaRelativo === 2 => 'Domingo',
            $diaRelativo === -1 => '1 día antes',
            default            => abs($diaRelativo) . ' días antes',
        };
    }



    // La fecha y hora exactas de la tarea en un retiro dado
    public function limiteEn(Retiro $retiro): Carbon
    {
        return $retiro->fecha_inicio->copy()
            ->addDays($this->dia_relativo)
            ->setTimeFromTimeString($this->hora);
    }



    public function cumplidas(): HasMany
    {
        return $this->hasMany(TareaCumplida::class, 'tarea_cronograma_id');
    }
}
