<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactoInforme extends Model
{
    protected $fillable = [
        'nombre',
        'celular',
        'retiro_id',
    ];



    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }
}
