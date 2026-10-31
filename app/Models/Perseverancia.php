<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perseverancia extends Model
{
    protected $fillable = [
        'tipo',
        'dia',
        'hora',
        'parroquia',
        'direccion',
        'orden',
    ];
}
