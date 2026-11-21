<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

//  El nombre (santo) que lleva cada mesa según su número, por grupo.
// No depende del retiro: sirve para los próximos.
class NombreMesa extends Model
{
    //  Imágenes que sube Secretaría (privadas) y las que trae la app (públicas)
    public const CARPETA_IMAGENES = 'santos';
    public const IMAGENES_APP     = 'img/santos';

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



    // "Santa Lucía" → "santa-lucia": así se llama la imagen del santo
    public static function clave(string $nombre): string
    {
        return Str::slug($nombre);
    }



    //  La imagen subida por Secretaría, si hay; si no, la que trae la app
    public static function rutaImagenSubida(string $nombre): ?string
    {
        $clave = static::clave($nombre);

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
            $ruta = self::CARPETA_IMAGENES . '/' . $clave . '.' . $extension;

            if (Storage::exists($ruta)) {
                return $ruta;
            }
        }

        return null;
    }



    public static function urlImagen(?string $nombre): ?string
    {
        if (blank($nombre)) {
            return null;
        }

        if (static::rutaImagenSubida($nombre)) {
            return route('mesas.imagenSanto', static::clave($nombre));
        }

        $publica = self::IMAGENES_APP . '/' . static::clave($nombre) . '.jpg';

        return file_exists(public_path($publica)) ? asset($publica) : null;
    }
}
