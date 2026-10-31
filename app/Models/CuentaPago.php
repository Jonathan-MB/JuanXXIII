<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaPago extends Model
{
    protected $fillable = [
        'entidad',
        'numero',
        'titular',
        'retiro_id',
    ];



    public function retiro(): BelongsTo
    {
        return $this->belongsTo(Retiro::class, 'retiro_id');
    }
}
