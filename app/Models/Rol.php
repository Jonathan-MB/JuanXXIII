<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{

    //  Nombres exactos de los roles. Los permisos se revisan por
    // nombre y no por id, para que no dependan del orden del seeder.
    public const ADMINISTRACION = 'Administración';
    public const SECRETARIA     = 'Secretaría';
    public const DIRECTIVOS     = 'Directivos';
    public const TESORERIA      = 'Tesorería';
    public const EQUIPO         = 'Equipo';

    protected $fillable = [
        'nombre',
    ];



    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }
}
