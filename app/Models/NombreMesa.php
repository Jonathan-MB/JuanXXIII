<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//  El nombre (santo) que lleva cada mesa según su número, por grupo.
// No depende del retiro: sirve para los próximos.
class NombreMesa extends Model
{
    protected $table = 'nombres_mesa';

    protected $fillable = [
        'sexo',
        'numero',
        'nombre',
    ];



    public static function para(string $sexo, int $numero): ?string
    {
        return static::where('sexo', $sexo)->where('numero', $numero)->value('nombre');
    }
}
