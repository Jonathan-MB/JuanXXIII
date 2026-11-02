<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{

    // Tallas de camiseta, en el orden en que se cuentan e imprimen
    public const TALLAS = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '4XL', '5XL'];

    protected $fillable = [
        'nombres',
        'apellidos',
        'cedula',
        'sexo',
        'fecha_nacimiento',
        'correo',
        'celular',
        'direccion',
        'barrio',
        'ciudad',
        'eps',
        'medicamentos',
        'talla_camiseta',
        'bautismo',
        'primera_comunion',
        'confirmacion',
        'matrimonio',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'bautismo'         => 'boolean',
        'primera_comunion' => 'boolean',
        'confirmacion'     => 'boolean',
        'matrimonio'       => 'boolean',
    ];



    //  "1.130.456.221", "1130456221" y "1130 456 221" son la misma
    // cédula. Se quitan puntos, espacios y guiones; si queda una letra,
    // la validación la rechaza (la cédula es solo números).
    public static function normalizarCedula(?string $cedula): string
    {
        return preg_replace('/[\s.\-]/', '', (string) $cedula);
    }



    //  Celulares y teléfonos se guardan solo con dígitos: "315 572-4732"
    // queda "3155724732". Las letras se dejan para que la validación las
    // rechace en vez de borrarlas sin avisar.
    public static function normalizarTelefono(?string $telefono): ?string
    {
        $limpio = preg_replace('/[\s.\-()]/', '', (string) $telefono);

        return $limpio === '' ? null : $limpio;
    }



    //  Quien ya terminó un retiro (aquí o antes de usar la aplicación)
    // no puede volver a inscribirse.
    // Quien no asistió o abandonó sí puede.
    public function yaHizoRetiro(): bool
    {
        return $this->inscripciones()->where('estado', Inscripcion::TERMINO)->exists()
            || RetiristaAnterior::where('cedula', $this->cedula)->exists();
    }



    public function getNombreCompletoAttribute(): string
    {
        return $this->nombres . ' ' . $this->apellidos;
    }



    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'persona_id');
    }
}
