<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactoEmergencia extends Model
{
    protected $fillable = [
        'nombre',
        'celular',
        'telefono_fijo',
        'correo',
        'parentesco',
        'inscripcion_id',
    ];



    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }
}
