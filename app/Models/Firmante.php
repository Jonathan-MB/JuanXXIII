<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firmante extends Model
{
    protected $fillable = [
        'nombre',
        'cargo',
        'celular',
        'orden',
    ];
}
