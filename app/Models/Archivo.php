<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Archivo extends Model
{

    // Las categorías, con el nombre que se le muestra a la persona
    public const CATEGORIAS = [
        'documentos' => 'Documentos',
        'charlas'    => 'Charlas',
        'letreros'   => 'Letreros',
    ];

    // Tipos permitidos: lo que hay en las carpetas del retiro
    public const EXTENSIONES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp'];

    // Tamaño máximo de cada archivo, en kilobytes (20 MB)
    public const TAMANO_MAXIMO = 20480;

    protected $fillable = [
        'categoria',
        'titulo',
        'descripcion',
        'numero',
        'ruta',
        'nombre_original',
        'tipo_mime',
        'tamano',
        'usuario_id',
    ];



    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->nombre_original, PATHINFO_EXTENSION));
    }



    //  Los PDF y las imágenes se abren en el navegador (para imprimir);
    // Word, Excel y PowerPoint solo se pueden descargar.
    public function seVeEnNavegador(): bool
    {
        return in_array($this->extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp'], true);
    }



    // "Word", "PDF"… para la etiqueta de cada archivo
    public function getTipoTextoAttribute(): string
    {
        return match ($this->extension) {
            'pdf'                        => 'PDF',
            'doc', 'docx'                => 'Word',
            'xls', 'xlsx'                => 'Excel',
            'ppt', 'pptx'                => 'PowerPoint',
            'jpg', 'jpeg', 'png', 'webp' => 'Imagen',
            default                      => strtoupper($this->extension),
        };
    }



    public function getTamanoTextoAttribute(): string
    {
        return $this->tamano >= 1048576
            ? number_format($this->tamano / 1048576, 1, ',', '.') . ' MB'
            : max(1, round($this->tamano / 1024)) . ' KB';
    }



    //  "002 - quien eres hombres.pdf" → número 2 y título "Quien eres hombres".
    // Así las charlas numeradas quedan en orden sin escribir nada a mano.
    public static function tituloDesdeNombre(string $nombreArchivo): array
    {
        $nombre = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        $nombre = trim(str_replace(['^', '_'], ['', ' '], $nombre));
        $numero = null;

        if (preg_match('/^(\d{1,3})\s*[-.)]\s*(.+)$/u', $nombre, $partes)) {
            $numero = (int) $partes[1];
            $nombre = $partes[2];
        }

        // Quita el " (1)" que agrega Windows a las copias
        $nombre = trim(preg_replace('/\s*\(\d+\)$/u', '', $nombre));

        return [$numero, mb_strtoupper(mb_substr($nombre, 0, 1)) . mb_substr($nombre, 1)];
    }



    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
