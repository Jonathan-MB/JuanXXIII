<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActividadRetiro extends Model
{
    protected $fillable = [
        'sexo',
        'inicio',
        'fin',
        'nombre',
        'es_charla',
        'charlista',
        'retiro_id',
    ];

    protected $casts = [
        'inicio'    => 'datetime',
        'fin'       => 'datetime',
        'es_charla' => 'boolean',
    ];



    //  0 viernes, 1 sábado, 2 domingo. Lo que empieza de madrugada
    // cuenta como de la jornada anterior.
    public function jornadaEn(Retiro $retiro): int
    {
        return (int) $retiro->fecha_inicio->copy()->startOfDay()->diffInDays($this->inicio->copy()->subHours(4)->startOfDay());
    }



    //  La fecha real de una hora de la jornada. Si la hora es de
    // madrugada, cae al día siguiente.
    public static function momento(Retiro $retiro, int $jornada, string $hora): Carbon
    {
        $momento = $retiro->fecha_inicio->copy()->addDays($jornada)->setTimeFromTimeString($hora);

        if ($hora < ActividadBase::HORA_CORTE) {
            $momento->addDay();
        }

        return $momento;
    }



    // Si termina "antes" de empezar, es que termina después de medianoche
    public static function finDesde(Carbon $inicio, ?string $horaFin): ?Carbon
    {
        if (! $horaFin) {
            return null;
        }

        $fin = $inicio->copy()->setTimeFromTimeString($horaFin);

        return $fin->lte($inicio) ? $fin->addDay() : $fin;
    }



    // Minutos que dura, para la columna "tiempo"
    public function getDuracionAttribute(): ?int
    {
        return $this->fin ? (int) $this->inicio->diffInMinutes($this->fin) : null;
    }



    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }
}
