<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetiristaAnterior extends Model
{
    protected $fillable = [
        'cedula',
        'nombre',
        'sexo',
        'numero_retiro',
        'anio',
        'celular',
    ];

    protected $casts = [
        'numero_retiro' => 'integer',
        'anio'          => 'integer',
    ];
}
