<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perseverancia extends Model
{
    // La carpeta privada de las imágenes; se ven pasando por una ruta
    public const CARPETA_IMAGENES = 'perseverancias';

    protected $fillable = [
        'tipo',
        'sector',
        'dia',
        'hora',
        'parroquia',
        'direccion',
        'mapa',
        'latitud',
        'longitud',
        'actividades',
        'imagen',
        'imagen_credito',
        'en_inicio',
        'orden',
    ];

    protected $casts = [
        'en_inicio' => 'boolean',
        'orden'     => 'integer',
        'latitud'   => 'float',
        'longitud'  => 'float',
    ];



    // "3.4372, -76.5225", como las copia Google Maps; vacío si no tiene punto
    public function getCoordenadasAttribute(): string
    {
        return $this->latitud !== null && $this->longitud !== null ? $this->latitud . ', ' . $this->longitud : '';
    }


    //  Lo que se hace ese día, un renglón por actividad:
    // "6:30 p. m. Rosario", "7:00 p. m. Sagrada Eucaristía"…
    public function getListaActividadesAttribute(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $this->actividades))));
    }



    //  Lo que se busca en Google Maps: lo escrito en "mapa" o, si está
    // vacío, la parroquia con la dirección en Cali
    public function getBusquedaMapaAttribute(): string
    {
        return $this->mapa ?: collect([$this->parroquia, $this->direccion, 'Cali, Valle del Cauca'])->filter()->implode(', ');
    }



    // "El Rodeo" o, si no tiene sector, la parroquia
    public function getTituloAttribute(): string
    {
        return $this->sector ?: $this->parroquia;
    }
}
