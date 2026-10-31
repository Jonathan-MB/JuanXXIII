<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    protected $fillable = [
        'nombre',
        'usuario',
        'correo',
        'contrasena',
        'activo',
        'rol_id',
        'equipo_id',
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];



    public function getAuthPassword()
    {
        return $this->contrasena;
    }



    // Hashear la contraseña automáticamente
    public function setContrasenaAttribute($value)
    {
        $this->attributes['contrasena'] = Hash::make($value);
    }



    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol?->nombre, $roles, true);
    }



    //  Administración y Secretaría ven el inventario de todos los
    // equipos; cualquier otra cuenta, solo el de su propio equipo.
    public function puedeVerInventario(Equipo $equipo): bool
    {
        return $this->tieneRol(Rol::ADMINISTRACION, Rol::SECRETARIA) || $this->equipo_id === $equipo->id;
    }



    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }



    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }
}
