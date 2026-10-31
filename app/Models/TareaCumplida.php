<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TareaCumplida extends Model
{
    protected $fillable = [
        'hecha_en',
        'tarea_cronograma_id',
        'retiro_id',
        'usuario_id',
    ];

    protected $casts = [
        'hecha_en' => 'datetime',
    ];



    public function tarea(): BelongsTo
    {
        return $this->belongsTo(TareaCronograma::class, 'tarea_cronograma_id');
    }



    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
