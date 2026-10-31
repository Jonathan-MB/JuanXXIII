<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = [
        'valor',
        'fecha',
        'medio',
        'comprobante',
        'nota',
        'inscripcion_id',
        'usuario_id',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'fecha' => 'date',
    ];



    public function getValorTextoAttribute(): string
    {
        return '$' . number_format((float) $this->valor, 0, ',', '.');
    }



    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }



    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
