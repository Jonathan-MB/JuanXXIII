<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{
    protected $fillable = [
        'nombre',
        'cantidad_personas',
    ];



    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'equipo_id');
    }



    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class, 'equipo_id');
    }
}
