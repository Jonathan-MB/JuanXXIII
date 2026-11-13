<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Retiro extends Model
{
    protected $fillable = [
        'numero_hombres',
        'numero_mujeres',
        'fecha_inicio',
        'hora_inicio',
        'fecha_fin',
        'hora_fin',
        'ofrenda',
        'que_incluye',
        'porcentaje_reserva',
        'fecha_limite_pago',
        'lugar',
        'direccion',
        'ciudad',
        'inscripciones_abiertas',
        'es_proximo',
        'es_jovenes',
    ];

    protected $casts = [
        'fecha_inicio'           => 'date',
        'fecha_fin'              => 'date',
        'fecha_limite_pago'      => 'date',
        'ofrenda'                => 'decimal:2',
        'inscripciones_abiertas' => 'boolean',
        'es_proximo'             => 'boolean',
        'es_jovenes'             => 'boolean',
    ];

    //  Si a alguien se le quedan abiertas las inscripciones, se cierran
    // solas estos días después de que termina el retiro
    public const DIAS_PARA_CERRAR = 2;

    // Para revisar los cierres una sola vez por visita
    private static bool $alDia = false;



    //  Cierra los retiros que terminaron hace DIAS_PARA_CERRAR días y
    // siguen abiertos; si cerró alguno, abre el marcado como próximo. El
    // próximo deja de serlo cuando empieza. Se llama al buscar el retiro
    // actual o el abierto, así no hace falta una tarea programada.
    public static function ponerAlDia(): void
    {
        if (self::$alDia) {
            return;
        }

        self::$alDia = true;

        $vencidos = self::where('inscripciones_abiertas', true)
            ->whereDate('fecha_fin', '<=', now()->subDays(self::DIAS_PARA_CERRAR)->toDateString())
            ->get();

        foreach ($vencidos as $vencido) {
            $vencido->update(['inscripciones_abiertas' => false]);
            $vencido->abrirElProximo();
        }

        self::where('es_proximo', true)
            ->whereDate('fecha_inicio', '<=', now()->toDateString())
            ->update(['es_proximo' => false]);
    }



    //  Al cerrarse este retiro se abren las inscripciones del marcado
    // como próximo, si es posterior y sigue cerrado
    public function abrirElProximo(): void
    {
        self::where('es_proximo', true)
            ->where('inscripciones_abiertas', false)
            ->whereDate('fecha_inicio', '>', $this->fecha_inicio->toDateString())
            ->update(['inscripciones_abiertas' => true]);
    }



    //  El que se anuncia en las cartas de este retiro: el marcado como
    // próximo o, si no hay, el siguiente por fecha. Null si no hay ninguno.
    public function siguiente(): ?self
    {
        $posteriores = self::whereDate('fecha_inicio', '>', $this->fecha_fin->toDateString());

        return (clone $posteriores)->where('es_proximo', true)->first()
            ?? $posteriores->orderBy('fecha_inicio')->first();
    }



    //  El retiro que se muestra en la página pública: el abierto a
    // inscripciones que empiece primero. Si no hay ninguno, null.
    public static function proximoAbierto(): ?self
    {
        self::ponerAlDia();

        return self::where('inscripciones_abiertas', true)
            ->whereDate('fecha_fin', '>=', now()->toDateString())
            ->orderBy('fecha_inicio')
            ->with(['cuentasPago', 'contactosInformes'])
            ->first();
    }



    //  El retiro de trabajo del panel: el próximo que no ha terminado,
    // esté o no abierto a inscripciones.
    public static function actual(): ?self
    {
        self::ponerAlDia();

        return self::whereDate('fecha_fin', '>=', now()->toDateString())
            ->orderBy('fecha_inicio')
            ->first();
    }



    // "20, 21 y 22 de noviembre"
    public function getDiasTextoAttribute(): string
    {
        $dias = [];
        for ($dia = $this->fecha_inicio->copy(); $dia->lte($this->fecha_fin); $dia->addDay()) {
            $dias[] = $dia->day;
        }

        $ultimo = array_pop($dias);
        $texto  = $dias ? implode(', ', $dias) . ' y ' . $ultimo : (string) $ultimo;

        return $texto . ' de ' . $this->fecha_fin->locale('es')->translatedFormat('F');
    }



    // "Retiro 91 Hombres · 90 Mujeres"
    public function getNombreAttribute(): string
    {
        return 'Retiro ' . $this->numero_hombres . ' Hombres · ' . $this->numero_mujeres . ' Mujeres';
    }



    public function getOfrendaTextoAttribute(): string
    {
        return '$' . number_format((float) $this->ofrenda, 0, ',', '.');
    }



    public function cuentasPago(): HasMany
    {
        return $this->hasMany(CuentaPago::class, 'retiro_id');
    }



    public function contactosInformes(): HasMany
    {
        return $this->hasMany(ContactoInforme::class, 'retiro_id');
    }



    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'retiro_id');
    }



    public function mesas(): HasMany
    {
        return $this->hasMany(Mesa::class, 'retiro_id');
    }



    public function rectorados(): HasMany
    {
        return $this->hasMany(Rectorado::class, 'retiro_id');
    }



    public function actividades(): HasMany
    {
        return $this->hasMany(ActividadRetiro::class, 'retiro_id');
    }



    public function santisimoFranjas(): HasMany
    {
        return $this->hasMany(SantisimoFranja::class, 'retiro_id');
    }



    // "Masculino" -> el número de hombres; "Femenino" -> el de mujeres
    public function numeroPara(string $sexo): int
    {
        return $sexo === 'Femenino' ? $this->numero_mujeres : $this->numero_hombres;
    }
}
