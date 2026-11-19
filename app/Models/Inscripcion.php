<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscripcion extends Model
{

    // Estados posibles. Una inscripción nunca se borra: cambia de estado.
    public const INSCRITO   = 'inscrito';
    public const CONFIRMADO = 'confirmado';
    public const LLEGO      = 'llego';
    public const TERMINO    = 'termino';
    public const ABANDONO   = 'abandono';
    public const NO_ASISTIO = 'no_asistio';

    protected $fillable = [
        'estado',
        'forma_pago',
        'comprobante',
        'tiene_padrino',
        'padrino_nombre',
        'padrino_telefono',
        'padrino_presente',
        'padrino_parroquia',
        'acepta_datos',
        'declara_requisitos',
        'confirmacion_enviada',
        'silla',
        'llegada_en',
        'persona_id',
        'retiro_id',
        'mesa_id',
    ];

    protected $casts = [
        'tiene_padrino'        => 'boolean',
        'acepta_datos'         => 'boolean',
        'declara_requisitos'   => 'boolean',
        'llegada_en'           => 'datetime',
        'confirmacion_enviada' => 'datetime',
    ];

    // Cómo se le muestra cada estado a la persona que usa el panel
    public const NOMBRES_ESTADO = [
        self::INSCRITO   => 'Inscrito',
        self::CONFIRMADO => 'Datos confirmados',
        self::LLEGO      => 'Llegó',
        self::TERMINO    => 'Terminó',
        self::ABANDONO   => 'Abandonó',
        self::NO_ASISTIO => 'No asistió',
    ];

    // Estados que sacan a la persona de las listas del retiro
    public const ESTADOS_FUERA = [self::ABANDONO, self::NO_ASISTIO];



    public function getNombreEstadoAttribute(): string
    {
        return self::NOMBRES_ESTADO[$this->estado] ?? $this->estado;
    }



    public function estaFuera(): bool
    {
        return in_array($this->estado, self::ESTADOS_FUERA, true);
    }



    //  Usa pagos_sum_valor cuando la consulta lo trajo con
    // withSum('pagos', 'valor'), para no hacer una consulta por fila.
    public function getTotalPagadoAttribute(): float
    {
        if (array_key_exists('pagos_sum_valor', $this->attributes)) {
            return (float) $this->attributes['pagos_sum_valor'];
        }

        return (float) $this->pagos()->sum('valor');
    }



    //  Por qué medio pagó: los medios de los pagos registrados ("Nequi 315…,
    // Efectivo") o, si aún no hay pagos, el que dijo al inscribirse
    public function getMedioPagoAttribute(): string
    {
        $medios = $this->pagos->pluck('medio')->filter()->unique()->values();

        if ($medios->isNotEmpty()) {
            return $medios->implode(', ');
        }

        // Sin el "a nombre de…", que en la lista ocupa mucho
        return $this->forma_pago ? 'Dijo: ' . \Illuminate\Support\Str::before($this->forma_pago, ' a nombre de') : '';
    }



    public function getSaldoAttribute(): float
    {
        return max(0, (float) $this->retiro->ofrenda - $this->total_pagado);
    }



    // completo, abono o sin_pago, comparado contra la ofrenda del retiro
    public function getEstadoPagoAttribute(): string
    {
        if ($this->total_pagado <= 0) {
            return 'sin_pago';
        }

        return $this->total_pagado >= (float) $this->retiro->ofrenda ? 'completo' : 'abono';
    }



    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }



    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }



    public function contactosEmergencia(): HasMany
    {
        return $this->hasMany(ContactoEmergencia::class, 'inscripcion_id');
    }



    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }



    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'inscripcion_id');
    }
}
