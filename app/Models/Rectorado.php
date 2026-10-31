<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rectorado extends Model
{

    //  Los cargos del rectorado en el orden en que se imprimen. El CR
    // (catequista rotativo) es opcional y puede haber hasta dos.
    public const CARGOS = [
        'Rector(a)',
        'Vicerrector(a)',
        'Dirigente responsable',
        'Catequista rotativo (CR)',
        'Catequista rotativo (CR)',
    ];

    protected $fillable = [
        'sexo',
        'cargo',
        'nombre',
        'ciudad',
        'telefono',
        'orden',
        'retiro_id',
    ];



    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }
}
